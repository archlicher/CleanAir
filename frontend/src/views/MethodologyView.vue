<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="methodology">
    <header class="methodology__hero">
      <p class="eyebrow">Methodology & limitations</p>
      <h1>How Clean Air Finder ranks cities</h1>
      <p>
        Clean Air Finder compares Bulgarian cities using validated historical PM2.5 measurements
        published by the European Environment Agency. The goal is a transparent, city-level
        comparison — not a real-time air-quality index or a personal exposure estimate.
      </p>
    </header>

    <div class="methodology__grid">
      <section class="method-card">
        <span class="method-card__step">01</span>
        <h2>Hourly observations</h2>
        <p>
          PM2.5 concentrations are collected as hourly values for Bulgarian monitoring stations.
          Completed historical years use validated E1a data. The current year may use up-to-date,
          unverified E2a data.
        </p>
      </section>

      <section class="method-card">
        <span class="method-card__step">02</span>
        <h2>Station-day eligibility</h2>
        <p>
          A station contributes a daily mean only when it has at least 18 valid hourly
          observations — 75% of a 24-hour day. Negative or non-numeric values are discarded.
        </p>
      </section>

      <section class="method-card">
        <span class="method-card__step">03</span>
        <h2>City daily mean</h2>
        <p>
          Each eligible station gets one daily mean. The city daily mean is the arithmetic average
          of those station means, so every station carries equal weight regardless of how many
          hours it reported.
        </p>
      </section>

      <section class="method-card">
        <span class="method-card__step">04</span>
        <h2>Ranking metric</h2>
        <p>
          The ranking metric is the arithmetic mean of eligible city daily means across the chosen
          period. A city is included only when at least 75% of days in the period have a city daily
          value. Cities are ordered from lowest to highest PM2.5.
        </p>
      </section>
    </div>

    <section class="method-panel">
      <h2>What the numbers mean</h2>
      <p>
        PM2.5 is fine particulate matter with a diameter of 2.5 micrometres or less. Lower
        concentrations generally indicate cleaner air. The colour bands used in the interface
        (good, moderate, elevated, poor, very poor) are indicative communication aids. They are not
        a legal classification and do not replace the applicable national or EU assessment methods.
      </p>
      <div class="band-scale" aria-label="Indicative PM2.5 colour bands">
        <span class="band-scale__item band-scale__item--good">≤ 10 Good</span>
        <span class="band-scale__item band-scale__item--moderate">≤ 20 Moderate</span>
        <span class="band-scale__item band-scale__item--elevated">≤ 25 Elevated</span>
        <span class="band-scale__item band-scale__item--poor">≤ 50 Poor</span>
        <span class="band-scale__item band-scale__item--very-poor">&gt; 50 Very poor</span>
      </div>
    </section>

    <section class="method-panel">
      <h2>Coverage and date limits</h2>
      <ul>
        <li>Queries are limited to the most recent 12 months and a maximum of 366 days.</li>
        <li>
          A ranking returns no cities when every city falls below the 75% date-coverage threshold.
          This usually means the selected period is too short or not yet fully reported.
        </li>
        <li>
          Coverage protects against ranking a city on a handful of exceptional days. It does not
          correct for differences in station siting or local pollution sources.
        </li>
      </ul>
    </section>

    <section class="method-panel">
      <h2>Important limitations</h2>
      <ul>
        <li>
          The comparison depends on the official station-to-city mapping and on the quality of the
          source data. Monitoring networks are not evenly distributed across cities.
        </li>
        <li>
          A city average is not the same as the air a person breathes. Exposure varies by
          neighbourhood, time spent outdoors and indoor conditions.
        </li>
        <li>
          Historical measurements can be revised by the data provider. Results are recalculated from
          the imported dataset and cached for a short period.
        </li>
        <li>
          The current-year dataset may be unverified and can change after validation. Treat it as
          provisional.
        </li>
        <li>
          This application is an indicative comparison tool. It is not a legal-compliance
          assessment, health advice or an official government publication.
        </li>
      </ul>
    </section>

    <section class="method-panel method-panel--source">
      <h2>Source and attribution</h2>
      <p>
        Air-quality data comes from the
        <a href="https://eeadmz1-downloads-webapp.azurewebsites.net/" target="_blank" rel="noopener noreferrer">
          European Environment Agency Air Quality Download Service
        </a>
        . Please review the EEA terms of use and attribution requirements before redistributing the
        data or derived rankings.
      </p>
      <p class="method-panel__note">
        No synthetic measurements are presented as real observations. City metadata is a factual
        list of Bulgarian cities; station metadata must be imported from an official source before
        collection.
      </p>
    </section>

    <RouterLink class="button button--secondary methodology__back" to="/">← Back to ranking</RouterLink>
  </div>
</template>

<style scoped>
.methodology {
  display: grid;
  gap: 20px;
  max-width: 900px;
}

.methodology__hero {
  padding: 16px 0 6px;
}

.methodology__hero h1 {
  max-width: 22ch;
  margin: 8px 0 14px;
}

.methodology__hero p:last-child {
  max-width: 72ch;
  margin: 0;
  color: var(--muted);
  font-size: 15px;
  line-height: 1.7;
}

.methodology__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.method-card,
.method-panel {
  padding: 24px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  box-shadow: var(--shadow-sm);
}

.method-card__step {
  display: inline-block;
  padding: 3px 9px;
  border-radius: 999px;
  background: var(--brand-soft);
  color: var(--brand-strong);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.method-card h2,
.method-panel h2 {
  margin: 12px 0 8px;
  font-size: 18px;
  letter-spacing: -0.02em;
}

.method-card p,
.method-panel p {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.7;
}

.method-panel ul {
  display: grid;
  gap: 9px;
  margin: 12px 0 0;
  padding-left: 20px;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.7;
}

.method-panel li::marker {
  color: var(--brand);
}

.band-scale {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
}

.band-scale__item {
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}

.band-scale__item--good {
  background: var(--tone-good-soft);
  color: var(--tone-good-ink);
}

.band-scale__item--moderate {
  background: var(--tone-moderate-soft);
  color: var(--tone-moderate-ink);
}

.band-scale__item--elevated {
  background: var(--tone-elevated-soft);
  color: var(--tone-elevated-ink);
}

.band-scale__item--poor {
  background: var(--tone-poor-soft);
  color: var(--tone-poor-ink);
}

.band-scale__item--very-poor {
  background: var(--tone-very-poor-soft);
  color: var(--tone-very-poor-ink);
}

.method-panel a {
  color: var(--brand-strong);
  font-weight: 600;
}

.method-panel--source {
  background: linear-gradient(150deg, var(--surface), var(--brand-soft));
}

.method-panel__note {
  margin-top: 14px !important;
  padding-top: 14px;
  border-top: 1px solid var(--border);
  font-size: 12px !important;
}

.methodology__back {
  justify-self: start;
}

@media (max-width: 720px) {
  .methodology__grid {
    grid-template-columns: 1fr;
  }
}
</style>
