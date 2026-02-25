import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  categoryService,
  type Category,
  type CreateCategoryData,
  type UpdateCategoryData,
} from '@/services/categoryService'

export const useCategoryStore = defineStore('category', () => {
  const defaultCategories = ref<Category[]>([])
  const userCategories = ref<Category[]>([])
  const allCategories = ref<Category[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const hasCategories = computed(() => allCategories.value.length > 0)

  async function fetchCategories() {
    loading.value = true
    error.value = null

    try {
      const response = await categoryService.getAll()
      defaultCategories.value = response.default
      userCategories.value = response.user
      allCategories.value = response.all
      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка завантаження категорій'
      console.error('Fetch categories error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  async function createCategory(data: CreateCategoryData) {
    loading.value = true
    error.value = null

    try {
      const newCategory = await categoryService.create(data)
      userCategories.value.push(newCategory)
      allCategories.value.push(newCategory)
      return newCategory
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка створення категорії'
      console.error('Create category error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateCategory(id: number, data: UpdateCategoryData) {
    loading.value = true
    error.value = null

    try {
      const response = await categoryService.update(id, data)
      const updatedCategory = response.category

      const userIndex = userCategories.value.findIndex((c) => c.id === id)
      if (userIndex !== -1) {
        userCategories.value[userIndex] = updatedCategory
      }

      const allIndex = allCategories.value.findIndex((c) => c.id === id)
      if (allIndex !== -1) {
        allCategories.value[allIndex] = updatedCategory
      }

      return updatedCategory
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка оновлення категорії'
      console.error('Update category error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  async function deleteCategory(id: number) {
    loading.value = true
    error.value = null

    try {
      await categoryService.delete(id)

      // Видаляємо з масивів
      userCategories.value = userCategories.value.filter((c) => c.id !== id)
      allCategories.value = allCategories.value.filter((c) => c.id !== id)

      return true
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Помилка видалення категорії'
      console.error('Delete category error:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  function getCategoryById(id: number): Category | undefined {
    return allCategories.value.find((c) => c.id === id)
  }

  function getCategoryBySlug(slug: string): Category | undefined {
    return allCategories.value.find((c) => c.slug === slug)
  }

  function clearError() {
    error.value = null
  }

  return {
    defaultCategories,
    userCategories,
    allCategories,
    loading,
    error,
    hasCategories,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    getCategoryById,
    getCategoryBySlug,
    clearError,
  }
})
