<script setup lang="ts">
import { computed } from 'vue'
import { pm25Band } from '@/lib/airQuality'
import { formatNumber } from '@/lib/date'

const props = defineProps<{
  value: number | null
  unit?: string
}>()

const band = computed(() => (props.value === null ? null : pm25Band(props.value)))
</script>

<template>
  <span v-if="band && value !== null" class="pm-badge" :class="`pm-badge--${band.tone}`">
    {{ formatNumber(value) }} {{ unit ?? 'µg/m³' }}
    <span class="pm-badge__label">{{ band.label }}</span>
  </span>
</template>

<style scoped>
.pm-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 5px 11px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}

.pm-badge__label {
  padding-left: 7px;
  border-left: 1px solid currentColor;
  font-weight: 600;
  opacity: 0.8;
}

.pm-badge--good {
  background: var(--tone-good-soft);
  color: var(--tone-good-ink);
}

.pm-badge--moderate {
  background: var(--tone-moderate-soft);
  color: var(--tone-moderate-ink);
}

.pm-badge--elevated {
  background: var(--tone-elevated-soft);
  color: var(--tone-elevated-ink);
}

.pm-badge--poor {
  background: var(--tone-poor-soft);
  color: var(--tone-poor-ink);
}

.pm-badge--very-poor {
  background: var(--tone-very-poor-soft);
  color: var(--tone-very-poor-ink);
}
</style>
