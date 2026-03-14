<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import { useToast } from '@/composables/useToast'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import type { CreateExpenseData } from '@/services/expenseService'
import type { AxiosError } from 'axios'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import ExpenseForm from '@/components/ExpenseForm.vue'

const router = useRouter()
const route = useRoute()
const expenseStore = useExpenseStore()
const toast = useToast()
const { marginLeft } = useSidebarMargin()

const isEdit = computed(() => !!route.params.id)
const expenseId = computed(() => (route.params.id ? Number(route.params.id) : null))

const initialLoading = ref(false)
const formLoading = ref(false)
const loadError = ref<string | null>(null)
const validationErrors = ref<Record<string, string[]>>({})

const initialData = ref<Partial<CreateExpenseData> | undefined>(undefined)

onMounted(async () => {
  await loadInitialData()
})

async function loadInitialData() {
  initialLoading.value = true
  loadError.value = null

  try {
    if (isEdit.value && expenseId.value) {
      const expense = await expenseStore.getExpenseById(expenseId.value)
      if (expense) {
        initialData.value = {
          amount: expense.amount,
          currency: expense.currency || 'UAH',
          converted_amount: expense.converted_amount || expense.amount,
          exchange_rate: expense.exchange_rate || 1.0,
          date: expense.date,
          description: expense.description || '',
          category_id: expense.category?.id,
        }
      } else {
        loadError.value = 'Витрату не знайдено'
      }
    }
  } catch (error: unknown) {
    loadError.value = error instanceof Error ? error.message : 'Помилка завантаження даних'
  } finally {
    initialLoading.value = false
  }
}

async function handleSubmit(data: CreateExpenseData) {
  validationErrors.value = {}
  expenseStore.clearError()
  formLoading.value = true

  try {
    let result = null

    if (isEdit.value && expenseId.value) {
      result = await expenseStore.updateExpense(expenseId.value, data)
    } else {
      result = await expenseStore.createExpense(data)
    }

    if (result) {
      toast.success(isEdit.value ? 'Витрату успішно оновлено!' : 'Витрату успішно додано!')
      router.push('/expenses')
    }
  } catch (error) {
    const axiosError = error as AxiosError<{ errors?: Record<string, string[]>; message?: string }>

    if (axiosError.response?.status === 422 && axiosError.response.data.errors) {
      validationErrors.value = axiosError.response.data.errors
      toast.error('Будь ласка, виправте помилки у формі')
    } else if (axiosError.response?.data?.message) {
      toast.error(axiosError.response.data.message)
    } else if (axiosError.message === 'Network Error') {
      toast.error("Помилка мережі. Перевірте з'єднання з інтернетом.")
    } else {
      toast.error(isEdit.value ? 'Не вдалося оновити витрату' : 'Не вдалося створити витрату')
    }
  } finally {
    formLoading.value = false
  }
}

function handleCancel() {
  router.push('/expenses')
}

async function handleRetry() {
  // Retry is handled by ExpenseForm component
}
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader :title="isEdit ? 'Редагувати витрату' : 'Додати витрату'" back-to="/expenses" />

        <div v-if="initialLoading" class="loading">Завантаження...</div>

        <div v-else-if="loadError" class="error-message">
          {{ loadError }}
          <button @click="loadInitialData" class="btn-retry">Спробувати знову</button>
        </div>

        <ExpenseForm
          v-else
          :title="isEdit ? 'Редагувати витрату' : 'Додати витрату'"
          :initial-data="initialData"
          :loading="formLoading"
          :validation-errors="validationErrors"
          @submit="handleSubmit"
          @cancel="handleCancel"
          @retry="handleRetry"
        />
      </div>
    </main>
  </div>
</template>

<style scoped>
.page-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-primary);
}

.main-content {
  flex: 1;
  transition: margin-left 0.3s ease;
}

.content-wrapper {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
  font-size: 18px;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 20px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-retry {
  margin-top: 10px;
  padding: 10px 20px;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-retry:hover {
  opacity: 0.9;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }
}
</style>
