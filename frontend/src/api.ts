import axios, { AxiosError } from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  timeout: 20000,
})

export type City = {
  id: number
  name: string
  slug: string
  latitude: number | null
  longitude: number | null
  stations_count?: number
}

export type CityResult = {
  city_id: number
  city: string
  city_slug: string
  average_pm25: number
  covered_days: number
  coverage_percent: number
  station_count: number
  unit: string
}

export type Ranking = {
  pollutant: string
  unit: string
  from: string
  to: string
  period_days: number
  winner: CityResult | null
  ranking: CityResult[]
  methodology: {
    metric: string
    minimum_coverage_percent: number
    interpretation: string
  }
}

export type SeriesPoint = {
  date: string
  mean_value: number
  station_count: number
  coverage_ratio: number
}

export type CitySeries = {
  city: Pick<City, 'id' | 'name' | 'slug'>
  pollutant: string
  unit: string
  from: string
  to: string
  summary: {
    average_pm25: number | null
    days_with_data: number
    coverage_percent: number
    station_count: number
  }
  series: SeriesPoint[]
}

type LaravelError = {
  message?: string
  errors?: Record<string, string[]>
}

export async function fetchRanking(from: string, to: string): Promise<Ranking> {
  const { data } = await api.get<Ranking>('/air-quality/ranking', { params: { from, to } })
  return data
}

export async function fetchCitySeries(slug: string, from: string, to: string): Promise<CitySeries> {
  const { data } = await api.get<CitySeries>(`/air-quality/cities/${encodeURIComponent(slug)}/series`, {
    params: { from, to },
  })
  return data
}

export async function fetchCities(): Promise<City[]> {
  const { data } = await api.get<City[]>('/cities')
  return data
}

export function errorMessage(error: unknown, fallback: string): string {
  if (error instanceof AxiosError) {
    const payload = error.response?.data as LaravelError | undefined
    const firstValidationError = payload?.errors ? Object.values(payload.errors).flat()[0] : undefined

    return firstValidationError || payload?.message || fallback
  }

  return fallback
}

export function errorStatus(error: unknown): number | undefined {
  return error instanceof AxiosError ? error.response?.status : undefined
}
