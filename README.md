# Clean Air Finder

Laravel + Vue 3 application for ranking Bulgarian cities by historical PM2.5 measurements.

## Important data note

The EEA Air Quality Download Service provides zipped Parquet time series. Its May 2026 documentation describes:
- `POST /ParquetFile` for zipped Parquet downloads (600 MB response limit)
- `POST /ParquetFile/async` for asynchronous large downloads
- `POST /ParquetFile/urls` for a CSV of per-sampling-point Parquet URLs
- dataset values: `1` = E2a up-to-date/unverified; `2` = E1a validated; `3` = historical Airbase
- hourly data uses `aggregationType: "hour"`.

This implementation deliberately uses E1a (validated) for completed historical years and E2a only for the current year. The adapter downloads EEA files, but the Parquet-to-domain mapping must be checked against the actual EEA Parquet schema and metadata for Bulgaria before production ingestion. Do not treat the project as populated with real data until the import has been run and verified.

Source documentation:
https://eeadmz1-downloads-webapp.azurewebsites.net/content/documentation/How_To_Downloads.pdf
EEA portal: https://eeadmz1-downloads-webapp.azurewebsites.net/

## Stack

- Laravel 12 / PHP 8.3
- Vue 3 + TypeScript + Vite
- PostgreSQL 16
- Redis 7
- Python 3 + PyArrow for Parquet extraction
- Docker Compose

## Run

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan air-quality:collect --from=2025-01-01 --to=2025-12-31
docker compose exec app php artisan air-quality:aggregate
```

Open http://localhost:8080.

Run queue worker:

```bash
docker compose exec app php artisan queue:work --tries=3
```

Run scheduler locally:

```bash
docker compose exec app php artisan schedule:work
```

Production scheduler should run `php artisan schedule:run` every minute. Run at least one queue worker. For multiple scheduler instances, use a shared cache and `onOneServer()`.

## Data methodology

- Pollutant: PM2.5, concentration in µg/m³.
- Source: EEA Air Quality Download Service.
- Ranking metric: arithmetic mean of eligible daily city PM2.5 means over the selected period.
- Each station contributes one daily mean; city daily mean is the arithmetic mean of eligible station daily means (equal station weighting).
- A station-day requires at least 18 valid hourly observations (75% of 24).
- A city is eligible only when at least 75% of days in the requested period have a city daily value.
- Only valid, non-negative concentrations are imported.
- This is an indicative city comparison, not a personal exposure or legal-compliance assessment.

The city/station metadata import is a separate prerequisite. The included seed data contains no fabricated cities or measurements. Add official Bulgarian station metadata before collecting observations.

## API

- `GET /api/health`
- `GET /api/cities`
- `GET /api/air-quality/ranking?from=YYYY-MM-DD&to=YYYY-MM-DD`
- `GET /api/air-quality/cities/{city}/series?from=...&to=...`

Allowed period: within the most recent 12 months, maximum 366 days. Requests with invalid dates return 422. Requests without sufficient coverage return 404.

## Tests

```bash
php artisan test
npm run build
```

## Operational notes / remaining deployment work

Before exposing this publicly:
1. Verify EEA terms and attribution for the exact downloaded dataset.
2. Import and validate official Bulgarian station/sampling-point metadata, including coordinates and city mapping.
3. Run the EEA adapter against a small known sampling point; verify columns, units, timezone and validity/verification codes in the actual Parquet files.
4. Configure real secrets, TLS, backups, alerting and a managed observability destination.
5. Load-test the API with k6 and record p50/p95/p99 for cached and uncached 12-month queries.
6. Configure object storage for archived Parquet exports and test restore procedures.

## AI usage note

AI assistance was used to draft scaffolding, tests and documentation. The developer must verify the EEA schema, licensing, station-to-city mapping, statistical methodology and all generated code. No synthetic measurements are presented as real observations.
