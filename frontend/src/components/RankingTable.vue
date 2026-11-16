<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { CityResult } from '@/api'
import { pm25Band } from '@/lib/airQuality'
import { formatDateRange, formatNumber, formatPercent } from '@/lib/date'

const props = defineProps<{
  ranking: CityResult[]
  from: string
  to: string
}>()

const maxAverage = computed(() => Math.max(...props.ranking.map((city) => city.average_pm25), 1))

function barWidth(value: number): string {
  return `${Math.max(8, Math.round((value / maxAverage.value) * 100))}%`
}

function medal(index: number): string | null {
  return ['🥇', '🥈', '🥉'][index] ?? null
}
</script>

<template>
  <div class="ranking-card">
    <div class="ranking-card__head">
      <div>
        <p class="eyebrow">City ranking</p>
        <h2>Cleanest to most polluted</h2>
      </div>
      <p class="ranking-card__period">{{ formatDateRange(from, to) }}</p>
    </div>

    <div class="ranking-table-wrap">
      <table class="ranking-table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">City</th>
            <th scope="col">Average PM2.5</th>
            <th scope="col">Coverage</th>
            <th scope="col">Stations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(city, index) in ranking" :key="city.city_id">
            <td class="ranking-table__rank">
              <span class="rank-badge" :class="{ 'rank-badge--top': index < 3 }">
                {{ medal(index) ?? index + 1 }}
              </span>
            </td>
            <td>
              <RouterLink
                class="city-link"
                :to="{ name: 'city', params: { slug: city.city_slug }, query: { from, to } }"
              >
                {{ city.city }}
              </RouterLink>
            </td>
            <td class="ranking-table__value">
              <div class="value-row">
                <strong>{{ formatNumber(city.average_pm25) }}</strong>
                <span class="unit">µg/m³</span>
                <span class="band-dot" :class="`band-dot--${pm25Band(city.average_pm25).tone}`"></span>
              </div>
              <div class="bar-track" aria-hidden="true">
                <span class="bar-fill" :style="{ width: barWidth(city.average_pm25) }"></span>
              </div>
            </td>
            <td>
              <span class="coverage-pill">{{ formatPercent(city.coverage_percent) }}</span>
              <span class="coverage-days">{{ city.covered_days }} days</span>
            </td>
            <td>{{ city.station_count }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.ranking-card {
  padding: 24px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.ranking-card__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.ranking-card h2 {
  margin: 4px 0 0;
  font-size: 20px;
  letter-spacing: -0.02em;
}

.ranking-card__period {
  margin: 0;
  color: var(--muted);
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.ranking-table-wrap {
  overflow-x: auto;
  margin: 0 -8px;
  padding: 0 8px;
}

.ranking-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 640px;
}

.ranking-table th {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
  color: var(--muted);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.09em;
  text-align: left;
  text-transform: uppercase;
}

.ranking-table td {
  padding: 14px 12px;
  border-bottom: 1px solid var(--border-soft);
  font-size: 14px;
  vertical-align: middle;
}

.ranking-table tbody tr {
  transition: background 140ms ease;
}

.ranking-table tbody tr:hover {
  background: var(--surface-soft);
}

.ranking-table tbody tr:last-child td {
  border-bottom: 0;
}

.ranking-table__rank {
  width: 52px;
}

.rank-badge {
  display: inline-grid;
  place-items: center;
  min-width: 30px;
  height: 30px;
  padding: 0 6px;
  border-radius: 9px;
  background: var(--surface-soft);
  color: var(--muted);
  font-size: 12px;
  font-weight: 800;
}

.rank-badge--top {
  background: var(--brand-soft);
  color: var(--brand-strong);
}

.city-link {
  color: var(--ink);
  font-weight: 700;
  text-decoration: none;
}

.city-link:hover {
  color: var(--brand-strong);
  text-decoration: underline;
  text-underline-offset: 3px;
}

.ranking-table__value {
  min-width: 160px;
}

.value-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.value-row strong {
  font-size: 17px;
  font-variant-numeric: tabular-nums;
}

.unit {
  color: var(--muted);
  font-size: 11px;
}

.band-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-left: 2px;
}

.band-dot--good {
  background: var(--tone-good);
}

.band-dot--moderate {
  background: var(--tone-moderate);
}

.band-dot--elevated {
  background: var(--tone-elevated);
}

.band-dot--poor {
  background: var(--tone-poor);
}

.band-dot--very-poor {
  background: var(--tone-very-poor);
}

.bar-track {
  height: 5px;
  margin-top: 7px;
  border-radius: 999px;
  background: var(--surface-soft);
  overflow: hidden;
}

.bar-fill {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, var(--brand), var(--tone-elevated));
}

.coverage-pill {
  display: inline-block;
  padding: 3px 9px;
  border-radius: 999px;
  background: var(--brand-soft);
  color: var(--brand-strong);
  font-size: 11px;
  font-weight: 800;
}

.coverage-days {
  display: block;
  margin-top: 4px;
  color: var(--muted);
  font-size: 11px;
}

@media (max-width: 700px) {
  .ranking-card {
    padding: 18px;
  }

  .ranking-card__head {
    flex-direction: column;
  }
}
</style>
