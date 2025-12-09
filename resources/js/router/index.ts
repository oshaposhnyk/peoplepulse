import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: '/dashboard',
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/employees',
      name: 'employees',
      component: () => import('@/views/employee/EmployeeListView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/employees/:id',
      name: 'employee-detail',
      component: () => import('@/views/employee/EmployeeDetailView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/teams',
      name: 'teams',
      component: () => import('@/views/team/TeamListView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/teams/:id',
      name: 'team-detail',
      component: () => import('@/views/team/TeamDetailView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/equipment',
      name: 'equipment',
      component: () => import('@/views/equipment/EquipmentListView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/leaves',
      name: 'leaves',
      component: () => import('@/views/leave/LeaveListView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfileView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile/:id',
      name: 'profile-view',
      component: () => import('@/views/ProfileView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router

