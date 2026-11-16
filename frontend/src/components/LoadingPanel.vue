<script setup lang="ts">
withDefaults(
  defineProps<{
    label?: string
    rows?: number
  }>(),
  {
    label: 'Loading data…',
    rows: 4,
  },
)
</script>

<template>
  <div class="loading-panel" role="status" aria-live="polite">
    <span class="loading-panel__label">
      <span class="spinner" aria-hidden="true"></span>
      {{ label }}
    </span>
    <div class="loading-panel__skeleton">
      <span v-for="row in rows" :key="row" class="loading-panel__row" :style="{ width: `${100 - row * 9}%` }"></span>
    </div>
  </div>
</template>

<style scoped>
.loading-panel {
  display: grid;
  gap: 18px;
  padding: 24px;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
}

.loading-panel__label {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
}

.loading-panel__skeleton {
  display: grid;
  gap: 12px;
}

.loading-panel__row {
  display: block;
  height: 14px;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--surface-soft), var(--border-soft), var(--surface-soft));
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite linear;
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }

  to {
    background-position: -200% 0;
  }
}
</style>
