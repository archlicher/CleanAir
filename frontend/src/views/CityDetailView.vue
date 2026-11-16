<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import {
  errorMessage,
  errorStatus,
  fetchCitySeries,
  type CitySeries,
} from '@/api'
import CitySeriesChart from '@/components/CitySeriesChart.vue'
import DateRangeForm from '@/components/DateRangeForm.vue'
import EmptyState from '@/components/EmptyState.vue'
import LoadingPanel from '@/components/LoadingPanel.vue'
import Pm25Badge from '@/components/Pm25Badge.vue'
import StatCard from '@/components/StatCard.vue'
import {
  daysBetween,
  defaultRange,
  earliestSelectableDate,
  formatDate,
  formatDateRange,
  formatNumber,
  formatPercent,
  rangeForDays,
  toISODate,
  today,
} from '@/lib/date'

const route = useRoute()
const router = useRouter()

const slug = computed(() => String(route.params.slug ?? ''))
const minDate = toISODate(earliestSelectableDate())
const maxDate = toISODate(today())
const initial = defaultRange(30)
const isoDatePattern = /^\d{4}-\d{2}-\d{2}$/

function initialDate(value: unknown, fallback: string): string {
  return typeof value === 'string' && isoDatePattern.test(value) ? value : fallback
}

const from = ref(initialDate(route.query.from, initial.from))
const to = ref(initialDate(route.query.to, initial.to))
const loading = ref(false)
const error = ref('')
const validation = ref('')
const notFound = ref(false)
const data = ref<CitySeries | null>(null)

const cityName = computed(() => data.value?.city.name ?? slug.value.replace(/-/g, ' '))

const periodDays = computed(() => daysBetween(from.value, to.value))

const hasSeries = computed(() => (data.value?.series.length ?? 0) > 0)

function validate(): boolean {
  validation.value = ''

  if (!from.value || !to.value) {
    validation.value = 'Choose both a start and an end date.'
    return false
  }

  if (from.value > to.value) {
    validation.value = 'The start date must be on or before the end date.'
    return false
  }

  if (from.value < minDate || to.value > maxDate) {
    validation.value = 'Choose a period within the most recent 12 months.'
    return false
  }

  if (daysBetween(from.value, to.value) > 366) {
    validation.value = 'The selected period cannot be longer than 366 days.'
    return false
  }

  return true
}

async function load(): Promise<void> {
  error.value = ''
  notFound.value = false

  if (!validate()) {
    return
  }

  loading.value = true

  try {
    data.value = await fetchCitySeries(slug.value, from.value, to.value)
  } catch (caught) {
    data.value = null

    if (errorStatus(caught) === 404) {
      notFound.value = true
      return
    }

    error.value = errorMessage(caught, 'Unable to load this city’s air-quality data.')
  } finally {
    loading.value = false
  }
}

function applyFilters(): void {
  void router.replace({ name: 'city', params: { slug: slug.value }, query: { from: from.value, to: to.value } })
  void load()
}

function tryRange(days: number): void {
  const range = rangeForDays(days)
  from.value = range.from
  to.value = range.to
  applyFilters()
}

onMounted(() => {
  void load()
})

watch(slug, () => {
  const range = defaultRange(30)
  from.value = initialDate(route.query.from, range.from)
  to.value = initialDate(route.query.to, range.to)
  void load()
})
</script>

<template>
  <div class="city-page">
    <RouterLink to="/" class="back-link">← Back to city ranking</RouterLink>

    <template v-if="notFound">
      <EmptyState title="City not found" message="This city is not part of the supported Bulgarian dataset.">
        <RouterLink class="button button--primary" to="/">Back to ranking</RouterLink>
      </EmptyState>
    </template>

    <template v-else>
      <header class="city-header">
        <div>
          <p class="eyebrow">City detail · PM2.5</p>
          <h1 class="city-header__title">{{ cityName }}</h1>
          <p class="city-header__period">
            <template v-if="data">{{ formatDateRange(data.from, data.to) }}</template>
            <template v-else>{{ formatDateRange(from, to) }}</template>
          </p>
        </div>
        <Pm25Badge v-if="data?.summary.average_pm25 != null" :value="data.summary.average_pm25" />
      </header>

      <section class="filter-card">
        <DateRangeForm
          v-model:from="from"
          v-model:to="to"
          :loading="loading"
          submit-label="Update city view"
          @submit="applyFilters"
        />
        <p v-if="validation" class="notice notice--error" role="alert">{{ validation }}</p>
        <p v-else-if="error" class="notice notice--error" role="alert">{{ error }}</p>
      </section>

      <LoadingPanel v-if="loading" :label="`Loading ${cityName} daily values…`" />

      <template v-else-if="data">
        <section class="stats-grid" aria-label="Summary statistics">
          <StatCard
            label="Average PM2.5"
            :value="data.summary.average_pm25 === null ? '—' : `${formatNumber(data.summary.average_pm25)}`"
            :sub="data.summary.average_pm25 === null ? 'No measured days' : 'µg/m³ over measured days'"
            tone="good"
          />
          <StatCard
            label="Days with data"
            :value="`${data.summary.days_with_data}`"
            :sub="`of ${periodDays} days in period`"
          />
          <StatCard
            label="Date coverage"
            :value="formatPercent(data.summary.coverage_percent)"
            sub="eligible daily values"
          />
          <StatCard
            label="Stations"
            :value="`${data.summary.station_count}`"
            sub="contributing monitors"
          />
        </section>

        <template v-if="hasSeries">
          <section class="chart-card">
            <div class="chart-card__head">
              <div>
                <p class="eyebrow">Daily trend</p>
                <h2>Average PM2.5 by day</h2>
              </div>
              <p class="chart-card__hint">City average of eligible station daily means</p>
            </div>
            <CitySeriesChart :points="data.series" :unit="data.unit" />
          </section>

          <details class="data-table">
            <summary>View daily values</summary>
            <div class="data-table__scroll">
              <table>
                <thead>
                  <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Mean PM2.5</th>
                    <th scope="col">Stations</th>
                    <th scope="col">Hourly coverage</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="point in data.series" :key="point.date">
                    <td>{{ formatDate(point.date) }}</td>
                    <td><strong>{{ formatNumber(point.mean_value) }}</strong> µg/m³</td>
                    <td>{{ point.station_count }}</td>
                    <td>{{ formatPercent(point.coverage_ratio * 100) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </details>
        </template>

        <EmptyState
          v-else
          title="No daily values for this period"
          :message="`${cityName} has no eligible measured days in the selected range. Try a longer period or a different date range.`"
        >
          <div class="empty-actions">
            <button class="button button--primary" type="button" @click="tryRange(90)">Try 90 days</button>
            <button class="button button--secondary" type="button" @click="tryRange(365)">
              Try 12 months
            </button>
          </div>
        </EmptyState>

        <p class="methodology-note">
          Each point is the arithmetic mean of eligible station daily means for that city. A station
          day needs at least 18 valid hourly measurements. <RouterLink to="/methodology">Methodology</RouterLink>
        </p>
      </template>
    </template>
  </div>
</template>

<style scoped>
.city-page {
  display: grid;
  gap: 20px;
}

.back-link {
  justify-self: start;
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.back-link:hover {
  color: var(--brand-strong);
}

.city-header {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 20px;
  padding-top: 4px;
}

.city-header__title {
  margin: 6px 0 4px;
  font-size: clamp(38px, 6vw, 58px);
  letter-spacing: -0.05em;
  text-transform: capitalize;
}

.city-header__period {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
}

.filter-card {
  padding: 22px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.chart-card {
  padding: 24px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.chart-card__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
}

.chart-card h2 {
  margin: 3px 0 0;
  font-size: 20px;
  letter-spacing: -0.02em;
}

.chart-card__hint {
  max-width: 30ch;
  margin: 0;
  color: var(--muted);
  font-size: 11px;
  text-align: right;
}

.data-table {
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
}

.data-table summary {
  padding: 16px 20px;
  color: var(--brand-strong);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.data-table summary::marker {
  color: var(--brand);
}

.data-table__scroll {
  max-height: 420px;
  overflow: auto;
  border-top: 1px solid var(--border);
}

.data-table table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 10px 20px;
  border-bottom: 1px solid var(--border-soft);
  font-size: 13px;
  text-align: left;
  white-space: nowrap;
}

.data-table th {
  position: sticky;
  top: 0;
  background: var(--surface-soft);
  color: var(--muted);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.methodology-note {
  margin: 0;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.65;
}

.methodology-note a {
  color: var(--brand-strong);
  font-weight: 600;
}

.notice {
  margin: 16px 0 0;
  padding: 11px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
}

.notice--error {
  background: var(--error-bg);
  color: var(--error-ink);
}

.empty-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

@media (max-width: 860px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 620px) {
  .city-header,
  .chart-card__head {
    flex-direction: column;
    align-items: flex-start;
  }

  .chart-card__hint {
    text-align: left;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
