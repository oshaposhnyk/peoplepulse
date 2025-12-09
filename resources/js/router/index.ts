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
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/employees/:id',
      name: 'employee-detail',
      component: () => import('@/views/employee/EmployeeDetailView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/teams',
      name: 'teams',
      component: () => import('@/views/team/TeamListView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/teams/:id',
      name: 'team-detail',
      component: () => import('@/views/team/TeamDetailView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/equipment',
      name: 'equipment',
      component: () => import('@/views/equipment/EquipmentListView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
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
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
    return
  }

  // Check admin access
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    // Redirect non-admin users to dashboard if they try to access admin-only pages
    next({ name: 'dashboard' })
    return
  }

  // Redirect authenticated users away from guest-only pages
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }

  next()
})

export default router

