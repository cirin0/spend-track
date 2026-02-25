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
      component: ProfileView,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories',
      name: 'categories',
      component: CategoriesView,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/new',
      name: 'category-new',
      component: CategoryFormView,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/:id/edit',
      name: 'category-edit',
      component: CategoryFormView,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/:slug/expenses',
      name: 'category-expenses',
      component: CategoryExpensesView,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses',
      name: 'expenses',
      component: ExpensesView,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/new',
      name: 'expense-new',
      component: ExpenseFormView,
      meta: { requiresAuth: true },
    },
    {
      path: '/expenses/:id/edit',
      name: 'expense-edit',
      component: ExpenseFormView,
      meta: { requiresAuth: true },
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: AnalyticsView,
      meta: { requiresAuth: true },
    },
    {
      path: '/groups',
      name: 'groups',
      component: GroupsView,
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/new',
      name: 'group-new',
      component: GroupFormView,
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/:id',
      name: 'group-detail',
      component: GroupDetailView,
      meta: { requiresAuth: true },
    },
    {
      path: '/groups/:id/edit',
      name: 'group-edit',
      component: GroupFormView,
      meta: { requiresAuth: true },
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Ініціалізуємо auth store якщо ще не ініціалізовано
  if (!authStore.token) {
    authStore.initAuth()
  }

  const requiresAuth = to.meta.requiresAuth
  const requiresGuest = to.meta.requiresGuest
  const isAuthenticated = authStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    // Потрібна авторизація, але користувач не авторизований
    next('/login')
  } else if (requiresGuest && isAuthenticated) {
    // Сторінка тільки для гостей, але користувач авторизований
    next('/')
  } else {
    next()
  }
})

export default router
