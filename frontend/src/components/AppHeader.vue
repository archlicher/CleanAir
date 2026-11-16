<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <header class="site-header">
    <div class="site-header__inner">
      <RouterLink to="/" class="brand" aria-label="Clean Air Finder home">
        <span class="brand__mark" aria-hidden="true">
          <svg viewBox="0 0 32 32" fill="none">
            <path
              d="M5 19c3.5 0 5-2 8.5-2s5 2 8.5 2M8 13c2.6 0 3.8-1.6 6.4-1.6S18.2 13 20.8 13M14 7c2 0 2.9-1.2 4.9-1.2"
              stroke="currentColor"
              stroke-width="2.4"
              stroke-linecap="round"
            />
            <path d="M24.5 22.5c2.2-2.1 3.5-5 3.5-8.2" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
          </svg>
        </span>
        <span>
          <strong>Clean Air Finder</strong>
          <small>Bulgaria · PM2.5</small>
        </span>
      </RouterLink>

      <nav class="site-nav" aria-label="Main navigation">
        <RouterLink to="/" exact-active-class="is-active">Ranking</RouterLink>
        <RouterLink to="/methodology" active-class="is-active">Methodology</RouterLink>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  position: sticky;
  top: 0;
  z-index: 20;
  background: color-mix(in srgb, var(--surface) 86%, transparent);
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(14px);
}

.site-header__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  max-width: var(--page-max);
  margin: 0 auto;
  padding: 14px 24px;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  color: var(--ink);
  text-decoration: none;
}

.brand__mark {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  color: white;
  background: linear-gradient(140deg, var(--accent), var(--accent-strong));
  box-shadow: 0 8px 20px color-mix(in srgb, var(--accent) 30%, transparent);
}

.brand__mark svg {
  width: 26px;
}

.brand strong {
  display: block;
  font-size: 15px;
  letter-spacing: -0.01em;
}

.brand small {
  display: block;
  color: var(--muted);
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.site-nav {
  display: flex;
  align-items: center;
  gap: 4px;
}

.site-nav a {
  padding: 8px 14px;
  border-radius: 999px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: color 160ms ease, background 160ms ease;
}

.site-nav a:hover {
  color: var(--ink);
  background: var(--surface-soft);
}

.site-nav a.is-active {
  color: var(--brand-strong);
  background: var(--brand-soft);
}

@media (max-width: 520px) {
  .site-header__inner {
    padding: 12px 16px;
  }

  .brand small {
    display: none;
  }

  .site-nav a {
    padding: 8px 10px;
    font-size: 13px;
  }
}
</style>
