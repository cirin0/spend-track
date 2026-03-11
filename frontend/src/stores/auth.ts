import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  authService,
  type User,
  type LoginCredentials,
  type RegisterData,
} from '@/services/authService'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()

  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  function initAuth() {
    const savedToken = localStorage.getItem('jwt_token')
    const savedUser = localStorage.getItem('user')

    if (savedToken && savedUser) {
      token.value = savedToken
      try {
        user.value = JSON.parse(savedUser)
      } catch (e) {
        console.error('Error parsing saved user:', e)
        clearAuth()
      }
    }
  }

  async function login(credentials: LoginCredentials) {
    loading.value = true
    error.value = null

    try {
      const response = await authService.login(credentials)

      token.value = response.token
      user.value = response.user

      localStorage.setItem('jwt_token', response.token)
      localStorage.setItem('user', JSON.stringify(response.user))

      router.push('/')
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка входу'
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(data: RegisterData) {
    loading.value = true
    error.value = null

    try {
      await authService.register(data)

      return await login({ email: data.email, password: data.password })
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка реєстрації'
      console.error('Register error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    loading.value = true
    error.value = null

    try {
      const userData = await authService.me()
      user.value = userData
      localStorage.setItem('user', JSON.stringify(userData))
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка завантаження профілю'
      console.error('Fetch user error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true

    try {
      await authService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      clearAuth()
      loading.value = false
      router.push('/login')
    }
  }

  function clearAuth() {
    user.value = null
    token.value = null
    localStorage.removeItem('jwt_token')
    localStorage.removeItem('user')
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    initAuth,
    login,
    register,
    fetchUser,
    logout,
    clearAuth,
  }
})
