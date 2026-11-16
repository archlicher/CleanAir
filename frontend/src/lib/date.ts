const MONTHS_BACK = 12

export function toISODate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

export function today(): Date {
  const date = new Date()
  date.setHours(0, 0, 0, 0)
  return date
}

export function earliestSelectableDate(): Date {
  const date = today()
  date.setMonth(date.getMonth() - MONTHS_BACK)
  return date
}

export function defaultRange(days = 30): { from: string; to: string } {
  const end = today()
  const start = new Date(end)
  start.setDate(start.getDate() - (days - 1))

  const minimum = earliestSelectableDate()

  return {
    from: toISODate(start < minimum ? minimum : start),
    to: toISODate(end),
  }
}

export function rangeForDays(days: number): { from: string; to: string } {
  return defaultRange(days)
}

export function daysBetween(from: string, to: string): number {
  const start = new Date(`${from}T00:00:00`)
  const end = new Date(`${to}T00:00:00`)

  return Math.round((end.getTime() - start.getTime()) / 86_400_000) + 1
}

export function formatDate(iso: string, options: Intl.DateTimeFormatOptions = {}): string {
  if (!iso) {
    return ''
  }

  const date = new Date(`${iso}T00:00:00`)

  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    ...options,
  }).format(date)
}

export function formatDateRange(from: string, to: string): string {
  return `${formatDate(from)} – ${formatDate(to)}`
}

export function formatNumber(value: number, digits = 1): string {
  return new Intl.NumberFormat('en-GB', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits,
  }).format(value)
}

export function formatPercent(value: number, digits = 1): string {
  return `${formatNumber(value, digits)}%`
}
