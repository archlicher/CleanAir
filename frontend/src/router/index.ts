import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: 'Cleanest city ranking' },
    },
    {
      path: '/cities/:slug',
      name: 'city',
      component: () => import('../views/CityDetailView.vue'),
      meta: { title: 'City air quality' },
    },
    {
      path: '/methodology',
      name: 'methodology',
      component: () => import('../views/MethodologyView.vue'),
      meta: { title: 'Methodology' },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue'),
      meta: { title: 'Page not found' },
    },
  ],
  scrollBehavior(to, _from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }

    return { top: 0 }
  },
})

router.afterEach((to) => {
  const title = typeof to.meta.title === 'string' ? to.meta.title : 'Clean Air Finder'
  document.title = `${title} · Clean Air Finder`
})

export default router
