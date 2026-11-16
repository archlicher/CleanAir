<script setup lang="ts">
defineProps<{
  label: string
  value: string
  sub?: string
  tone?: 'default' | 'good'
}>()
</script>

<template>
  <div class="stat-card" :class="{ 'stat-card--good': tone === 'good' }">
    <span class="stat-card__label">{{ label }}</span>
    <strong class="stat-card__value">{{ value }}</strong>
    <span v-if="sub" class="stat-card__sub">{{ sub }}</span>
  </div>
</template>

<style scoped>
.stat-card {
  display: grid;
  gap: 4px;
  padding: 18px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
}

.stat-card__label {
  color: var(--muted);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.09em;
  text-transform: uppercase;
}

.stat-card__value {
  font-size: 28px;
  letter-spacing: -0.03em;
  font-variant-numeric: tabular-nums;
}

.stat-card__sub {
  color: var(--muted);
  font-size: 12px;
}

.stat-card--good {
  background: var(--brand-soft);
  border-color: color-mix(in srgb, var(--brand) 25%, var(--border));
}

.stat-card--good .stat-card__value {
  color: var(--brand-strong);
}
</style>
