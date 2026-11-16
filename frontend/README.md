# Clean Air Finder frontend

Vue 3 + TypeScript + Vite single-page application for comparing Bulgarian cities by historical
PM2.5 air-quality measurements.

## Development

```bash
npm install
npm run dev
```

The dev server proxies `/api` to `http://127.0.0.1:8000`. Set `VITE_API_URL` to use a different API
origin.

## Routes

- `/` — city ranking for a selected period
- `/cities/:slug` — daily PM2.5 series for one city
- `/methodology` — ranking methodology and limitations

## Checks

```bash
npm run build
npm run lint
```
