export type Pm25Band = {
  label: string
  tone: 'good' | 'moderate' | 'elevated' | 'poor' | 'very-poor'
}

/**
 * Indicative PM2.5 bands for comparing 24-hour city averages.
 * These are communication aids, not a legal compliance classification.
 */
export function pm25Band(value: number): Pm25Band {
  if (value <= 10) {
    return { label: 'Good', tone: 'good' }
  }

  if (value <= 20) {
    return { label: 'Moderate', tone: 'moderate' }
  }

  if (value <= 25) {
    return { label: 'Elevated', tone: 'elevated' }
  }

  if (value <= 50) {
    return { label: 'Poor', tone: 'poor' }
  }

  return { label: 'Very poor', tone: 'very-poor' }
}
