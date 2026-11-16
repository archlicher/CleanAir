<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="not-found">
    <p class="eyebrow">404</p>
    <h1>This page drifted away.</h1>
    <p>The page you are looking for does not exist. Head back to the city ranking to keep exploring.</p>
    <RouterLink class="button button--primary" to="/">Back to ranking</RouterLink>
  </div>
</template>

<style scoped>
.not-found {
  display: grid;
  justify-items: start;
  gap: 10px;
  max-width: 560px;
  padding: 48px 0;
}

.not-found h1 {
  margin: 0;
}

.not-found p:not(.eyebrow) {
  margin: 0 0 10px;
  color: var(--muted);
  line-height: 1.7;
}
</style>
