<script setup lang="ts">
defineProps<{
  title: string
  message: string
}>()
</script>

<template>
  <div class="empty-state">
    <span class="empty-state__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <path
          d="M4 15c3 0 4-2 7-2s4 2 7 2M6 10c2 0 3-1.5 5-1.5S14 10 16 10M11 5.5c1.5 0 2.3-1 3.8-1"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
        />
        <circle cx="19" cy="18" r="3" stroke="currentColor" stroke-width="1.8" />
      </svg>
    </span>
    <h2>{{ title }}</h2>
    <p>{{ message }}</p>
    <slot />
  </div>
</template>

<style scoped>
.empty-state {
  display: grid;
  justify-items: center;
  gap: 10px;
  padding: 48px 24px;
  border: 1px dashed var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
  text-align: center;
}

.empty-state__icon {
  display: grid;
  place-items: center;
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand-strong);
}

.empty-state__icon svg {
  width: 28px;
}

.empty-state h2 {
  margin: 0;
  font-size: 20px;
}

.empty-state p {
  max-width: 52ch;
  margin: 0;
  color: var(--muted);
  line-height: 1.6;
}
</style>
