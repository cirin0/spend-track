import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  groupService,
  type Group,
  type GroupCategory,
  type GroupExpense,
  type CreateGroupData,
  type UpdateGroupData,
  type CreateGroupCategoryData,
  type UpdateGroupCategoryData,
  type CreateGroupExpenseData,
  type UpdateGroupExpenseData,
  type GroupExpenseStatsResponse,
} from '@/services/groupService'

export const useGroupStore = defineStore('group', () => {
  const groups = ref<Group[]>([])
  const currentGroup = ref<Group | null>(null)
  const categories = ref<GroupCategory[]>([])
  const expenses = ref<GroupExpense[]>([])
  const expenseStats = ref<GroupExpenseStatsResponse | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const hasGroups = computed(() => groups.value.length > 0)

  async function fetchGroups() {
    loading.value = true
    error.value = null

    try {
      groups.value = await groupService.getAll()
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка завантаження груп'
      console.error('Fetch groups error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchGroupById(id: number) {
    loading.value = true
    error.value = null

    try {
      currentGroup.value = await groupService.getById(id)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка завантаження групи'
      console.error('Fetch group error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function createGroup(data: CreateGroupData) {
    loading.value = true
    error.value = null

    try {
      const newGroup = await groupService.create(data)
      groups.value.unshift(newGroup)
      return newGroup
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка створення групи'
      console.error('Create group error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateGroup(id: number, data: UpdateGroupData) {
    loading.value = true
    error.value = null

    try {
      const updatedGroup = await groupService.update(id, data)
      const index = groups.value.findIndex((g) => g.id === id)
      if (index !== -1) {
        groups.value[index] = updatedGroup
      }
      if (currentGroup.value?.id === id) {
        currentGroup.value = updatedGroup
      }
      return updatedGroup
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка оновлення групи'
      console.error('Update group error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function deleteGroup(id: number) {
    loading.value = true
    error.value = null

    try {
      await groupService.delete(id)
      groups.value = groups.value.filter((g) => g.id !== id)
      if (currentGroup.value?.id === id) {
        currentGroup.value = null
      }
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка видалення групи'
      console.error('Delete group error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function addMember(groupId: number, userId: number) {
    loading.value = true
    error.value = null

    try {
      const updatedGroup = await groupService.addMember(groupId, userId)
      if (currentGroup.value?.id === groupId) {
        currentGroup.value = updatedGroup
      }
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка додавання учасника'
      console.error('Add member error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function removeMember(groupId: number, userId: number) {
    loading.value = true
    error.value = null

    try {
      await groupService.removeMember(groupId, userId)
      if (currentGroup.value?.id === groupId && currentGroup.value.members) {
        currentGroup.value.members = currentGroup.value.members.filter((m) => m.id !== userId)
        currentGroup.value.members_count = currentGroup.value.members.length
      }
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка видалення учасника'
      console.error('Remove member error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function leaveGroup(groupId: number) {
    loading.value = true
    error.value = null

    try {
      await groupService.leave(groupId)
      groups.value = groups.value.filter((g) => g.id !== groupId)
      if (currentGroup.value?.id === groupId) {
        currentGroup.value = null
      }
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка виходу з групи'
      console.error('Leave group error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories(groupId: number) {
    loading.value = true
    error.value = null

    try {
      categories.value = await groupService.getCategories(groupId)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка завантаження категорій'
      console.error('Fetch categories error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function createCategory(groupId: number, data: CreateGroupCategoryData) {
    loading.value = true
    error.value = null

    try {
      const newCategory = await groupService.createCategory(groupId, data)
      categories.value.unshift(newCategory)
      return newCategory
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка створення категорії'
      console.error('Create category error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateCategory(
    groupId: number,
    categoryId: number,
    data: UpdateGroupCategoryData,
  ) {
    loading.value = true
    error.value = null

    try {
      const updatedCategory = await groupService.updateCategory(groupId, categoryId, data)
      const index = categories.value.findIndex((c) => c.id === categoryId)
      if (index !== -1) {
        categories.value[index] = updatedCategory
      }
      return updatedCategory
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка оновлення категорії'
      console.error('Update category error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function deleteCategory(groupId: number, categoryId: number) {
    loading.value = true
    error.value = null

    try {
      await groupService.deleteCategory(groupId, categoryId)
      categories.value = categories.value.filter((c) => c.id !== categoryId)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка видалення категорії'
      console.error('Delete category error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchExpenses(groupId: number) {
    loading.value = true
    error.value = null

    try {
      expenses.value = await groupService.getExpenses(groupId)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка завантаження витрат'
      console.error('Fetch expenses error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function fetchExpenseStats(groupId: number) {
    loading.value = true
    error.value = null

    try {
      expenseStats.value = await groupService.getExpenseStats(groupId)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка завантаження статистики'
      console.error('Fetch expense stats error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function createExpense(groupId: number, data: CreateGroupExpenseData) {
    loading.value = true
    error.value = null

    try {
      const newExpense = await groupService.createExpense(groupId, data)
      if (!Array.isArray(expenses.value)) {
        expenses.value = []
      }
      expenses.value.unshift(newExpense)
      return newExpense
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка створення витрати'
      console.error('Create expense error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateExpense(groupId: number, expenseId: number, data: UpdateGroupExpenseData) {
    loading.value = true
    error.value = null

    try {
      const updatedExpense = await groupService.updateExpense(groupId, expenseId, data)
      const index = expenses.value.findIndex((e) => e.id === expenseId)
      if (index !== -1) {
        expenses.value[index] = updatedExpense
      }
      return updatedExpense
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка оновлення витрати'
      console.error('Update expense error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function deleteExpense(groupId: number, expenseId: number) {
    loading.value = true
    error.value = null

    try {
      await groupService.deleteExpense(groupId, expenseId)
      expenses.value = expenses.value.filter((e) => e.id !== expenseId)
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { error?: string } } }
      error.value = axiosError.response?.data?.error || 'Помилка видалення витрати'
      console.error('Delete expense error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    groups,
    currentGroup,
    categories,
    expenses,
    expenseStats,
    loading,
    error,
    hasGroups,
    fetchGroups,
    fetchGroupById,
    createGroup,
    updateGroup,
    deleteGroup,
    addMember,
    removeMember,
    leaveGroup,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    fetchExpenses,
    fetchExpenseStats,
    createExpense,
    updateExpense,
    deleteExpense,
    clearError,
  }
})
