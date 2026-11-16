<template>
  <footer class="site-footer">
    <div class="site-footer__inner">
      <p>
        Data source:
        <a href="https://eeadmz1-downloads-webapp.azurewebsites.net/" target="_blank" rel="noopener noreferrer">
          European Environment Agency
        </a>
        · PM2.5 is fine particulate matter. Lower concentrations generally indicate cleaner air.
      </p>
      <p class="site-footer__meta">
        Historical comparison only · Not a personal exposure or legal-compliance assessment ·
        <RouterLink to="/methodology">Methodology</RouterLink>
      </p>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  border-top: 1px solid var(--border);
  background: var(--surface);
}

.site-footer__inner {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 8px 32px;
  max-width: var(--page-max);
  margin: 0 auto;
  padding: 24px;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.7;
}

.site-footer p {
  margin: 0;
  max-width: 60ch;
}

.site-footer__meta {
  text-align: right;
}

.site-footer a {
  color: var(--brand-strong);
  font-weight: 600;
}

@media (max-width: 720px) {
  .site-footer__meta {
    text-align: left;
  }
}
</style>
