# Clean Air Finder

Laravel + Vue 3 application for ranking Bulgarian cities by historical PM2.5 measurements from the
European Environment Agency (EEA).

The frontend is a single-page Vue app with:

- a city ranking for a selected period,
- a per-city daily PM2.5 chart and data table,
- a methodology and limitations page,
- responsive light/dark layouts and accessible states.

The backend exposes a small JSON API, imports EEA Parquet data, and builds station-day and city
aggregates.

## Stack

- Laravel 13 / PHP 8.4
- Vue 3 + TypeScript + Vite
- PostgreSQL 16
- Redis 7 (cache and queue)
- Python 3 + PyArrow for Parquet extraction
- Docker Compose, nginx, PHP-FPM

## Quick start with Docker

```bash
cp .env.example .env
docker compose up -d --build
```

Open <http://localhost:8080>. The API is available under the same origin at
<http://localhost:8080/api/health>.

On first boot the `app` container generates an application key, waits for PostgreSQL, runs
migrations and seeds the Bulgarian city list. The queue worker and scheduler run as separate
services.

Useful commands:

```bash
docker compose logs -f app
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan air-quality:aggregate
docker compose down
```

## Local development without Docker

Backend:

```bash
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

Frontend (in a second terminal):

```bash
cd frontend
npm install
npm run dev
```

Vite serves the app on <http://localhost:5173> and proxies `/api` to
`http://127.0.0.1:8000`. Override the API origin with `VITE_API_URL` when needed.

## Data pipeline

### 1. Import official station metadata

City metadata is seeded, but station metadata must come from an official source. Import a CSV with
this exact header:

```csv
city_slug,local_id,name,latitude,longitude
sofia,BG0001R,Official station name,42.6500,23.3800
```

```bash
php artisan air-quality:import-stations database/data/stations.csv
```

With Docker, copy the file into the container first:

```bash
docker compose cp stations.csv app:/tmp/stations.csv
docker compose exec app php artisan air-quality:import-stations /tmp/stations.csv
```

`local_id` must match the EEA Air Quality Station EoI code emitted by the Parquet reader (for example
`BG0045A`). The reader normalises the raw EEA sampling-point identifier
(`BG/SPO-BG0045A_06001_100`) to that code. Existing rows are updated by `local_id`.

### 2. Collect observations

```bash
php artisan air-quality:collect --from=2026-01-01 --to=2026-10-31
```

The command downloads the EEA ZIP, extracts Parquet rows with `scripts/read_parquet.py`, and stores
valid hourly PM2.5 observations. Each run is recorded in `data_collection_runs`.

Requires Python 3 with `pyarrow` installed (`pip install pyarrow`).

### 3. Build aggregates

```bash
php artisan air-quality:aggregate
```

This builds station-day means (minimum 18 valid hours per day) and clears the ranking cache. Use
`--fresh` to remove existing daily aggregates first.

The scheduler runs collection hourly, aggregation daily at 02:00, and archiving daily at 03:00.

## API

- `GET /api/health`
- `GET /api/cities`
- `GET /api/air-quality/ranking?from=YYYY-MM-DD&to=YYYY-MM-DD`
- `GET /api/air-quality/cities/{slug}/series?from=YYYY-MM-DD&to=YYYY-MM-DD`

Allowed period: within the most recent 12 months, maximum 366 days. Invalid dates or out-of-range
periods return `422`. A ranking with no city meeting the coverage threshold returns `404` with a
message and the empty result envelope.

Ranking response (abridged):

```json
{
  "pollutant": "PM2.5",
  "unit": "µg/m³",
  "from": "2026-01-01",
  "to": "2026-01-31",
  "period_days": 31,
  "winner": {
    "city_id": 1,
    "city": "Sofia",
    "city_slug": "sofia",
    "average_pm25": 12.4,
    "covered_days": 30,
    "coverage_percent": 96.8,
    "station_count": 4,
    "unit": "µg/m³"
  },
  "ranking": [],
  "methodology": {
    "metric": "Mean of daily city averages",
    "minimum_coverage_percent": 75,
    "interpretation": "Lower PM2.5 concentrations indicate cleaner air..."
  }
}
```

## Methodology

- Pollutant: PM2.5, concentration in µg/m³.
- Ranking metric: arithmetic mean of eligible daily city PM2.5 means over the selected period.
- Each station contributes one daily mean; the city daily mean is the arithmetic mean of eligible
  station daily means (equal station weighting).
- A station-day requires at least 18 valid hourly observations (75% of 24).
- A city is eligible only when at least 75% of days in the requested period have a city daily value.
- Only valid, non-negative concentrations are imported.
- Completed historical years use validated E1a data; the current year may use unverified E2a data.
- This is an indicative city comparison, not a personal exposure or legal-compliance assessment.

Colour bands in the interface are communication aids, not a legal classification.

## Tests and checks

```bash
cd backend && php artisan test --compact
cd backend && vendor/bin/pint --test
cd frontend && npm run build
cd frontend && npm run lint
```

## EEA data note

The EEA Air Quality Download Service provides zipped Parquet time series. The data API is served from
`https://eeadmz1-downloads-api-appservice.azurewebsites.net`; the older
`eeadmz1-downloads-webapp.azurewebsites.net` host now returns HTTP 403. Relevant endpoints:

- `POST /ParquetFile` for zipped Parquet downloads (600 MB response limit)
- `POST /ParquetFile/async` for asynchronous large downloads
- `POST /ParquetFile/urls` for per-sampling-point Parquet URLs
- `POST /List` for the sampling-point identifiers matching a query
- dataset values: `1` = E2a up-to-date/unverified; `2` = E1a validated; `3` = historical Airbase
- hourly data uses `aggregationType: "hour"`

This implementation uses E1a for completed historical years and E2a for the current year.

The Parquet schema was verified against live Bulgarian PM2.5 files. Each row exposes `Samplingpoint`
(`BG/SPO-BG0045A_06001_100`), `Start` (hour begin), `Value` (decimal), `Unit` (`ug.m-3`), `Validity`,
`Verification` and a numeric `Pollutant` code. `scripts/read_parquet.py` maps `Start` to `measured_at`,
casts the decimal value to a float and derives the station EoI code. `air-quality:collect` and
`air-quality:aggregate` were run end-to-end against real files and produced correct station-day means.

Station-to-city mapping is **not** contained in these Parquet files, and the EEA metadata service that
publishes it was unavailable at the time of writing. Do not publish rankings until official station
metadata (city and coordinates) has been imported; the city seed intentionally contains no station
identifiers.

Swagger/OpenAPI for the data API:
<https://eeadmz1-downloads-api-appservice.azurewebsites.net/swagger/index.html>

EEA portal: <https://eeadmz1-downloads-webapp.azurewebsites.net/> (currently returns HTTP 403)

## Operational notes / remaining deployment work

Before exposing this publicly:

1. Verify EEA terms and attribution for the exact downloaded dataset.
2. Import and validate official Bulgarian station/sampling-point metadata, including coordinates and
   city mapping.
3. Confirm the timezone convention of EEA `Start` timestamps and the `Validity`/`Verification` code
   semantics across datasets (columns, units and codes are verified for BG PM2.5).
4. Configure real secrets, TLS, backups, alerting and a managed observability destination.
5. Load-test the API with k6 and record p50/p95/p99 for cached and uncached 12-month queries.
6. Configure object storage for archived Parquet exports and test restore procedures.

The included city seed contains no fabricated measurements and no station identifiers. The
application is functional end-to-end but will show no ranking until station metadata and
observations have been imported.

## AI usage note

AI assistance was used to draft scaffolding, tests and documentation. The developer must verify the
EEA schema, licensing, station-to-city mapping, statistical methodology and all generated code. No
synthetic measurements are presented as real observations.
