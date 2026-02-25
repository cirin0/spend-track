import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  expenseService,
  type Expense,
  type CreateExpenseData,
  type UpdateExpenseData,
  type ExpenseStatsResponse,
} from '@/services/expenseService'

export const useExpenseStore = defineStore('expense', () => {
  const expenses = ref<Expense[]>([])
  const stats = ref<ExpenseStatsResponse | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const hasExpenses = computed(() => expenses.value.length > 0)
  const totalAmount = computed(() => expenses.value.reduce((sum, exp) => sum + exp.amount, 0))

  // Завантажити всі витрати
  async function fetchExpenses() {
    loading.value = true
    error.value = null

    try {
      expenses.value = await expenseService.getAll()
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка завантаження витрат'
      console.error('Fetch expenses error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  // Створити витрату
  async function createExpense(data: CreateExpenseData) {
    loading.value = true
    error.value = null

    try {
      const newExpense = await expenseService.create(data)
      // Переконуємося що expenses.value - це масив
      if (!Array.isArray(expenses.value)) {
        expenses.value = []
      }
      expenses.value.unshift(newExpense) // Додаємо на початок
      return newExpense
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка створення витрати'
      console.error('Create expense error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Оновити витрату
  async function updateExpense(id: number, data: UpdateExpenseData) {
    loading.value = true
    error.value = null

    try {
      const response = await expenseService.update(id, data)
      const updatedExpense = response.expense

      const index = expenses.value.findIndex((e) => e.id === id)
      if (index !== -1) {
        expenses.value[index] = updatedExpense
      }

      return updatedExpense
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка оновлення витрати'
      console.error('Update expense error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Видалити витрату
  async function deleteExpense(id: number) {
    loading.value = true
    error.value = null

    try {
      await expenseService.delete(id)
      expenses.value = expenses.value.filter((e) => e.id !== id)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка видалення витрати'
      console.error('Delete expense error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  // Отримати витрату по ID
  function getExpenseById(id: number): Expense | undefined {
    return expenses.value.find((e) => e.id === id)
  }

  // Отримати статистику витрат по категоріях
  async function fetchStats() {
    loading.value = true
    error.value = null

    try {
      stats.value = await expenseService.getStats()
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка завантаження статистики'
      console.error('Fetch stats error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  // Очистити помилку
  function clearError() {
    error.value = null
  }

  return {
    expenses,
    stats,
    loading,
    error,
    hasExpenses,
    totalAmount,
    fetchExpenses,
    createExpense,
    updateExpense,
    deleteExpense,
    getExpenseById,
    fetchStats,
    clearError,
  }
})
