<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  errorMessage,
  errorStatus,
  fetchRanking,
  type Ranking,
} from '@/api'
import DateRangeForm from '@/components/DateRangeForm.vue'
import EmptyState from '@/components/EmptyState.vue'
import LoadingPanel from '@/components/LoadingPanel.vue'
import Pm25Badge from '@/components/Pm25Badge.vue'
import RankingTable from '@/components/RankingTable.vue'
import {
  daysBetween,
  defaultRange,
  earliestSelectableDate,
  formatDateRange,
  formatNumber,
  formatPercent,
  rangeForDays,
  toISODate,
  today,
} from '@/lib/date'

const minDate = toISODate(earliestSelectableDate())
const maxDate = toISODate(today())
const initial = defaultRange(30)

const from = ref(initial.from)
const to = ref(initial.to)
const filter = ref('')
const loading = ref(false)
const error = ref('')
const validation = ref('')
const noCoverage = ref(false)
const result = ref<Ranking | null>(null)

const filteredRanking = computed(() => {
  const query = filter.value.trim().toLowerCase()
  const ranking = result.value?.ranking ?? []

  if (!query) {
    return ranking
  }

  return ranking.filter((city) => city.city.toLowerCase().includes(query))
})

const searchedRange = computed(() => {
  if (!result.value) {
    return ''
  }

  return formatDateRange(result.value.from, result.value.to)
})

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

async function search(): Promise<void> {
  error.value = ''
  noCoverage.value = false

  if (!validate()) {
    return
  }

  loading.value = true
  result.value = null
  filter.value = ''

  try {
    result.value = await fetchRanking(from.value, to.value)
  } catch (caught) {
    if (errorStatus(caught) === 404) {
      noCoverage.value = true
      error.value = errorMessage(
        caught,
        'No city has sufficient valid measurement coverage for this period.',
      )
    } else {
      error.value = errorMessage(caught, 'Unable to retrieve air-quality results. Please try again.')
    }
  } finally {
    loading.value = false
  }
}

function tryRange(days: number): void {
  const range = rangeForDays(days)
  from.value = range.from
  to.value = range.to
  void search()
}

onMounted(() => {
  void search()
})
</script>

<template>
  <div class="home">
    <section class="hero">
      <div class="hero__copy">
        <p class="eyebrow">Bulgaria · historical PM2.5</p>
        <h1>Find the city with the cleanest air.</h1>
        <p class="hero__lead">
          Compare Bulgarian cities by validated PM2.5 measurements over a chosen period. Lower
          concentrations indicate cleaner air. Results are indicative, not a legal compliance
          assessment.
        </p>
      </div>
      <div class="hero__stat" aria-hidden="true">
        <span>PM</span>
        <strong>2.5</strong>
        <small>fine particulate matter</small>
      </div>
    </section>

    <section class="search-card" aria-labelledby="search-heading">
      <div class="search-card__head">
        <div>
          <p class="eyebrow">Compare</p>
          <h2 id="search-heading">Choose a period</h2>
        </div>
        <p class="search-card__hint">Only periods inside the last 12 months can be ranked.</p>
      </div>

      <DateRangeForm
        v-model:from="from"
        v-model:to="to"
        :loading="loading"
        submit-label="Find cleanest city"
        @submit="search"
      />

      <p v-if="validation" class="notice notice--error" role="alert">{{ validation }}</p>
      <p v-else-if="error && !noCoverage" class="notice notice--error" role="alert">{{ error }}</p>
    </section>

    <LoadingPanel v-if="loading" label="Checking measured PM2.5 coverage…" />

    <template v-else-if="result?.winner">
      <section class="winner-card">
        <div class="winner-card__main">
          <p class="eyebrow">Cleanest city · {{ result.pollutant }}</p>
          <div class="winner-card__title">
            <h2>{{ result.winner.city }}</h2>
            <Pm25Badge :value="result.winner.average_pm25" :unit="result.winner.unit" />
          </div>
          <p class="winner-card__value">
            {{ formatNumber(result.winner.average_pm25) }}
            <small>µg/m³ average</small>
          </p>
          <p class="winner-card__period">{{ searchedRange }}</p>
        </div>

        <div class="winner-card__meta">
          <div>
            <span>Date coverage</span>
            <strong>{{ formatPercent(result.winner.coverage_percent) }}</strong>
            <small>{{ result.winner.covered_days }} of {{ result.period_days }} days</small>
          </div>
          <div>
            <span>Contributing stations</span>
            <strong>{{ result.winner.station_count }}</strong>
            <small>equal station weighting</small>
          </div>
          <RouterLink
            class="button button--secondary"
            :to="{
              name: 'city',
              params: { slug: result.winner.city_slug },
              query: { from: result.from, to: result.to },
            }"
          >
            View daily trend →
          </RouterLink>
        </div>
      </section>

      <section class="ranking-section">
        <div class="ranking-section__toolbar">
          <div>
            <p class="eyebrow">Results</p>
            <h2>{{ result.ranking.length }} eligible cities</h2>
          </div>
          <label class="search-input">
            <span class="visually-hidden">Filter cities</span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8" />
              <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
            </svg>
            <input v-model="filter" type="search" placeholder="Filter cities…" />
          </label>
        </div>

        <RankingTable
          v-if="filteredRanking.length"
          :ranking="filteredRanking"
          :from="result.from"
          :to="result.to"
        />
        <EmptyState
          v-else
          title="No matching city"
          :message="`No city in the current ranking matches “${filter}”.`"
        >
          <button class="button button--secondary" type="button" @click="filter = ''">Clear filter</button>
        </EmptyState>

        <p class="methodology-note">
          Method: {{ result.methodology.metric }}, with a minimum of
          {{ result.methodology.minimum_coverage_percent }}% date coverage.
          {{ result.methodology.interpretation }}
          <RouterLink to="/methodology">Read the methodology</RouterLink>.
        </p>
      </section>
    </template>

    <EmptyState
      v-else-if="noCoverage"
      title="Not enough measured data for this period"
      :message="error"
    >
      <div class="empty-actions">
        <button class="button button--primary" type="button" @click="tryRange(90)">Try 90 days</button>
        <button class="button button--secondary" type="button" @click="tryRange(365)">
          Try 12 months
        </button>
      </div>
    </EmptyState>

    <EmptyState
      v-else-if="!loading && !result"
      title="Ready when you are"
      message="Choose a date range and run the comparison. Only cities with at least 75% valid daily coverage are ranked."
    >
      <button class="button button--primary" type="button" @click="search">Run comparison</button>
    </EmptyState>
  </div>
</template>

<style scoped>
.home {
  display: grid;
  gap: 22px;
}

.hero {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 28px;
  align-items: center;
  padding: 18px 0 6px;
}

.hero h1 {
  max-width: 16ch;
  margin: 8px 0 14px;
}

.hero__lead {
  max-width: 62ch;
  margin: 0;
  color: var(--muted);
  font-size: 16px;
  line-height: 1.65;
}

.hero__stat {
  display: grid;
  justify-items: center;
  padding: 20px 26px;
  border: 1px solid var(--border);
  border-radius: 24px;
  background: linear-gradient(160deg, var(--surface), var(--brand-soft));
  box-shadow: var(--shadow-sm);
  text-align: center;
}

.hero__stat span {
  color: var(--brand-strong);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.16em;
}

.hero__stat strong {
  color: var(--brand-strong);
  font-size: 54px;
  line-height: 1;
  letter-spacing: -0.06em;
}

.hero__stat small {
  max-width: 13ch;
  margin-top: 4px;
  color: var(--muted);
  font-size: 10px;
  line-height: 1.4;
}

.search-card {
  padding: 24px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.search-card__head {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 20px;
}

.search-card__head h2 {
  margin: 3px 0 0;
  font-size: 21px;
  letter-spacing: -0.02em;
}

.search-card__hint {
  max-width: 28ch;
  margin: 0;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.5;
  text-align: right;
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

.winner-card {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(260px, 0.75fr);
  gap: 28px;
  align-items: center;
  padding: 30px;
  border: 1px solid color-mix(in srgb, var(--brand) 28%, var(--border));
  border-radius: var(--radius-lg);
  background:
    radial-gradient(circle at 88% 8%, color-mix(in srgb, var(--brand) 17%, transparent), transparent 42%),
    linear-gradient(150deg, var(--surface), var(--brand-soft));
  box-shadow: var(--shadow-md);
}

.winner-card__title {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}

.winner-card h2 {
  margin: 8px 0;
  font-size: clamp(34px, 5vw, 48px);
  letter-spacing: -0.04em;
}

.winner-card__value {
  display: flex;
  align-items: baseline;
  gap: 12px;
  margin: 4px 0 6px;
  color: var(--brand-strong);
  font-size: 50px;
  font-weight: 800;
  letter-spacing: -0.05em;
  line-height: 1;
}

.winner-card__value small {
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0;
}

.winner-card__period {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
}

.winner-card__meta {
  display: grid;
  gap: 14px;
  padding-left: 28px;
  border-left: 1px solid color-mix(in srgb, var(--brand) 18%, var(--border));
}

.winner-card__meta > div {
  display: grid;
  gap: 2px;
}

.winner-card__meta span {
  color: var(--muted);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.winner-card__meta strong {
  font-size: 22px;
  letter-spacing: -0.02em;
}

.winner-card__meta small {
  color: var(--muted);
  font-size: 11px;
}

.winner-card__meta .button {
  justify-self: start;
  margin-top: 4px;
}

.ranking-section {
  display: grid;
  gap: 16px;
}

.ranking-section__toolbar {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 16px;
}

.ranking-section__toolbar h2 {
  margin: 3px 0 0;
  font-size: 20px;
  letter-spacing: -0.02em;
}

.search-input {
  position: relative;
  display: flex;
  align-items: center;
  width: min(260px, 100%);
}

.search-input svg {
  position: absolute;
  left: 12px;
  width: 17px;
  color: var(--muted);
  pointer-events: none;
}

.search-input input {
  width: 100%;
  padding: 10px 12px 10px 37px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--surface);
  color: var(--ink);
  font: inherit;
  font-size: 13px;
}

.search-input input:focus {
  border-color: var(--brand);
  outline: 2px solid var(--brand-soft);
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

.empty-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

@media (max-width: 820px) {
  .hero {
    grid-template-columns: 1fr;
  }

  .hero__stat {
    display: none;
  }

  .winner-card {
    grid-template-columns: 1fr;
  }

  .winner-card__meta {
    padding-left: 0;
    padding-top: 20px;
    border-left: 0;
    border-top: 1px solid color-mix(in srgb, var(--brand) 18%, var(--border));
  }
}

@media (max-width: 620px) {
  .search-card__head,
  .ranking-section__toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-card__hint {
    max-width: none;
    text-align: left;
  }

  .search-input {
    width: 100%;
  }

  .winner-card {
    padding: 22px;
  }
}
</style>
