<script setup lang="ts">
import { earliestSelectableDate, rangeForDays, toISODate, today } from '@/lib/date'

const props = defineProps<{
  from: string
  to: string
  loading?: boolean
  submitLabel?: string
}>()

const emit = defineEmits<{
  'update:from': [value: string]
  'update:to': [value: string]
  submit: []
}>()

const min = toISODate(earliestSelectableDate())
const max = toISODate(today())

const presets = [
  { label: '7 days', days: 7 },
  { label: '30 days', days: 30 },
  { label: '90 days', days: 90 },
  { label: '12 months', days: 365 },
]

function updateFrom(event: Event): void {
  emit('update:from', (event.target as HTMLInputElement).value)
}

function updateTo(event: Event): void {
  emit('update:to', (event.target as HTMLInputElement).value)
}

function applyPreset(days: number): void {
  const range = rangeForDays(days)
  emit('update:from', range.from)
  emit('update:to', range.to)
  emit('submit')
}

function submit(): void {
  if (!props.loading) {
    emit('submit')
  }
}
</script>

<template>
  <form class="date-form" @submit.prevent="submit">
    <div class="date-form__fields">
      <label class="field">
        <span>From</span>
        <input :value="from" type="date" :min="min" :max="to || max" @input="updateFrom" />
      </label>

      <label class="field">
        <span>To</span>
        <input :value="to" type="date" :min="from || min" :max="max" @input="updateTo" />
      </label>

      <button class="button button--primary date-form__submit" type="submit" :disabled="loading">
        <span v-if="loading" class="spinner" aria-hidden="true"></span>
        {{ loading ? 'Checking data…' : (submitLabel ?? 'Find cleanest city') }}
      </button>
    </div>

    <div class="date-form__presets" aria-label="Quick date ranges">
      <span>Quick range</span>
      <button
        v-for="preset in presets"
        :key="preset.days"
        type="button"
        class="chip"
        :disabled="loading"
        @click="applyPreset(preset.days)"
      >
        {{ preset.label }}
      </button>
      <span class="date-form__hint">Up to 366 days within the last 12 months</span>
    </div>
  </form>
</template>

<style scoped>
.date-form {
  display: grid;
  gap: 18px;
}

.date-form__fields {
  display: grid;
  grid-template-columns: minmax(150px, 1fr) minmax(150px, 1fr) auto;
  gap: 14px;
  align-items: end;
}

.date-form__submit {
  min-height: 47px;
}

.date-form__presets {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  color: var(--muted);
  font-size: 12px;
}

.date-form__presets > span:first-child {
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  font-size: 10px;
}

.date-form__hint {
  margin-left: auto;
}

@media (max-width: 680px) {
  .date-form__fields {
    grid-template-columns: 1fr;
  }

  .date-form__hint {
    margin-left: 0;
  }
}
</style>
