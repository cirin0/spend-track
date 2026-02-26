<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import { useCategoryStore } from '@/stores/category'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import type { CreateExpenseData } from '@/services/expenseService'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const route = useRoute()
const expenseStore = useExpenseStore()
const categoryStore = useCategoryStore()
const { marginLeft } = useSidebarMargin()

const isEdit = computed(() => !!route.params.id)
const expenseId = computed(() => (route.params.id ? Number(route.params.id) : null))

const initialLoading = ref(false)
const formLoading = ref(false)
const loadError = ref<string | null>(null)

const form = ref({
  category_select: '',
  amount: 0,
  date: '',
  description: '',
})

const categoryId = computed(() => {
  if (!form.value.category_select) return 0
  return Number(form.value.category_select)
})

const errors = ref<Record<string, string>>({})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

watch(
  () => form.value.category_select,
  (newVal) => {
    console.log('Category select changed:', newVal)
    console.log('Parsed categoryId:', categoryId.value)
  },
)

onMounted(async () => {
  await loadInitialData()
})

async function loadInitialData() {
  initialLoading.value = true
  loadError.value = null

  try {
    if (!categoryStore.allCategories.length) {
      await categoryStore.fetchCategories()
    }

    if (isEdit.value && expenseId.value) {
      const expense = await expenseStore.getExpenseById(expenseId.value)
      if (expense) {
        form.value = {
          category_select: expense.category ? String(expense.category.id) : '',
          amount: expense.amount,
          date: expense.date,
          description: expense.description || '',
        }
      } else {
        loadError.value = 'Витрату не знайдено'
      }
    } else {
      const todayValue = today.value
      if (todayValue) {
        form.value.date = todayValue
      }
    }
  } catch (error: unknown) {
    loadError.value = error instanceof Error ? error.message : 'Помилка завантаження даних'
  } finally {
    initialLoading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  expenseStore.clearError()

  if (!form.value.amount || form.value.amount <= 0) {
    errors.value.amount = 'Введіть суму більше 0'
    return
  }

  if (!form.value.date) {
    errors.value.date = 'Виберіть дату'
    return
  }

  formLoading.value = true

  try {
    const data: CreateExpenseData = {
      amount: Number(form.value.amount),
      date: form.value.date,
    }

    if (categoryId.value) {
      data.category_id = categoryId.value
    }

    if (form.value.description) {
      data.description = form.value.description
    }

    let result = null

    if (isEdit.value && expenseId.value) {
      result = await expenseStore.updateExpense(expenseId.value, data)
    } else {
      result = await expenseStore.createExpense(data)
    }

    if (result) {
      router.push('/expenses')
    }
  } catch (error: unknown) {
    console.error('Form submission error:', error)
  } finally {
    formLoading.value = false
  }
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

        <div v-else class="form-card">
          <form @submit.prevent="handleSubmit" class="expense-form">
            <div class="form-group">
              <label for="category">Категорія</label>
              <select
                id="category"
                v-model="form.category_select"
                class="form-control"
                :class="{ error: errors.category_id }"
                :disabled="formLoading"
              >
                <option value="">Без категорії</option>
                <optgroup
                  v-if="categoryStore.defaultCategories.length > 0"
                  label="Системні категорії"
                >
                  <option
                    v-for="category in categoryStore.defaultCategories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.icon }} {{ category.name }}
                  </option>
                </optgroup>
                <optgroup v-if="categoryStore.userCategories.length > 0" label="Мої категорії">
                  <option
                    v-for="category in categoryStore.userCategories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.icon }} {{ category.name }}
                  </option>
                </optgroup>
              </select>
              <span v-if="errors.category_id" class="error-text">{{ errors.category_id }}</span>
              <div class="form-hint">
                Немає потрібної категорії?
                <router-link to="/categories/new" class="link">Створити нову</router-link>
              </div>
            </div>

            <div class="form-group">
              <label for="amount">Сума *</label>
              <div class="input-with-currency">
                <input
                  id="amount"
                  v-model.number="form.amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                  class="form-control"
                  :class="{ error: errors.amount }"
                  placeholder="0.00"
                  :disabled="formLoading"
                />
                <span class="currency">₴</span>
              </div>
              <span v-if="errors.amount" class="error-text">{{ errors.amount }}</span>
            </div>

            <div class="form-group">
              <label for="date">Дата *</label>
              <input
                id="date"
                v-model="form.date"
                type="date"
                required
                class="form-control"
                :class="{ error: errors.date }"
                :max="today"
                :disabled="formLoading"
              />
              <span v-if="errors.date" class="error-text">{{ errors.date }}</span>
            </div>

            <div class="form-group">
              <label for="description">Опис</label>
              <textarea
                id="description"
                v-model="form.description"
                class="form-control"
                :class="{ error: errors.description }"
                placeholder="Необов'язково: додайте опис витрати"
                rows="4"
                maxlength="500"
                :disabled="formLoading"
              ></textarea>
              <span v-if="errors.description" class="error-text">{{ errors.description }}</span>
              <div class="form-hint">{{ form.description.length }}/500 символів</div>
            </div>

            <div v-if="expenseStore.error" class="error-message">
              {{ expenseStore.error }}
            </div>

            <div class="form-actions">
              <button
                type="button"
                @click="router.push('/expenses')"
                class="btn-secondary"
                :disabled="formLoading"
              >
                Скасувати
              </button>
              <button type="submit" class="btn-primary" :disabled="formLoading">
                <span v-if="formLoading">...</span>
                <span v-else>{{ isEdit ? 'Зберегти' : 'Додати витрату' }}</span>
              </button>
            </div>
          </form>
        </div>
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

.form-card {
  background: var(--card-bg);
  padding: 32px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.expense-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

label {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.form-control {
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.2s;
  font-family: inherit;
  background: var(--input-bg);
  color: var(--text-primary);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

.form-control.error {
  border-color: var(--danger-color);
}

.form-control:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

select.form-control {
  cursor: pointer;
}

select.form-control:disabled {
  cursor: not-allowed;
}

textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.input-with-currency {
  position: relative;
}

.input-with-currency input {
  padding-right: 50px;
}

.currency {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-weight: 600;
  color: var(--primary-color);
  font-size: 18px;
}

.error-text {
  color: var(--danger-color);
  font-size: 13px;
  font-weight: 500;
}

.form-hint {
  font-size: 13px;
  color: var(--text-secondary);
}

.link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 14px 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--secondary-bg);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--hover-bg);
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .form-card {
    padding: 20px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
