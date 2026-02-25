import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import ProfileView from '@/views/ProfileView.vue'
import CategoriesView from '@/views/CategoriesView.vue'
import CategoryFormView from '@/views/CategoryFormView.vue'
import CategoryExpensesView from '@/views/CategoryExpensesView.vue'
import ExpensesView from '@/views/ExpensesView.vue'
import ExpenseFormView from '@/views/ExpenseFormView.vue'
import AnalyticsView from '@/views/AnalyticsView.vue'
import GroupsView from '@/views/GroupsView.vue'
import GroupFormView from '@/views/GroupFormView.vue'
import GroupDetailView from '@/views/GroupDetailView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
     {
      path: '/:pathMath(.*)*',
      name: 'NotFound',
      redirect: '/'
    },
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true },
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: import ('../views/ProfileView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/categories',
      name: 'categories',
      component: import('../views/CategoriesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/new',
      name: 'category-new',
      component: import ('../views/CategoryFormView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/:id/edit',
      name: 'category-edit',
      component: import('../views/CategoryFormView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/:slug/expenses',
      name: 'category-expenses',
      component: import('../views/CategoryExpensesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses',
      name: 'expenses',
      component: import('../views/ExpensesView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/new',
      name: 'expense-new',
      component: import('../views/ExpenseFormView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/:id/edit',
      name: 'expense-edit',
      component: import('../views/ExpenseFormView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: import('../views/AnalyticsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/groups',
      name: 'groups',
      component: import('../views/GroupsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/new',
      name: 'group-new',
      component: import('../views/GroupFormView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/:id',
      name: 'group-detail',
      component: import('../views/GroupDetailView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/:id/edit',
      name: 'group-edit',
      component: import('../views/GroupFormView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.token) {
    authStore.initAuth()
  }

  const requiresAuth = to.meta.requiresAuth
  const requiresGuest = to.meta.requiresGuest
  const isAuthenticated = authStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (requiresGuest && isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
