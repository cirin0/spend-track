<template>
  <div class="expense-form-container">
    <div class="expense-form-page">
      <PageHeader :title="isEdit ? 'Редагувати витрату' : 'Додати витрату'" back-to="/expenses" />

      <!-- Loading initial data -->
      <div v-if="initialLoading" class="loading">Завантаження...</div>

      <!-- Error -->
      <div v-else-if="loadError" class="error-message">
        {{ loadError }}
        <button @click="loadInitialData" class="btn-retry">Спробувати знову</button>
      </div>

      <!-- Form -->
      <form v-else @submit.prevent="handleSubmit" class="expense-form">
        <!-- Category Selection -->
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
            <optgroup v-if="categoryStore.defaultCategories.length > 0" label="Системні категорії">
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

        <!-- Amount -->
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

        <!-- Date -->
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

        <!-- Description -->
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

        <!-- Error Message -->
        <div v-if="expenseStore.error" class="error-message">
          {{ expenseStore.error }}
        </div>

        <!-- Actions -->
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
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import { useCategoryStore } from '@/stores/category'
import type { CreateExpenseData } from '@/services/expenseService'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const route = useRoute()
const expenseStore = useExpenseStore()
const categoryStore = useCategoryStore()

const isEdit = computed(() => !!route.params.id)
const expenseId = computed(() => (route.params.id ? Number(route.params.id) : null))

const initialLoading = ref(false)
const formLoading = ref(false)
const loadError = ref<string | null>(null)

const form = ref({
  category_select: '', // Combined "type-id" value for select
  amount: 0,
  date: '',
  description: '',
})

// Computed properties for parsed category
const categoryId = computed(() => {
  if (!form.value.category_select) return 0
  return Number(form.value.category_select)
})

const errors = ref<Record<string, string>>({})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Watch for debugging
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
    // Load categories
    if (!categoryStore.allCategories.length) {
      await categoryStore.fetchCategories()
    }

    // If edit mode, load expense data
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
      // Set default date to today
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
  // Reset errors
  errors.value = {}
  expenseStore.clearError()

  // Validate
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

    // Add category_id only if category is selected
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
    // Error is handled by store
    console.error('Form submission error:', error)
  } finally {
    formLoading.value = false
  }
}
</script>

<style scoped>
.expense-form-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.expense-form-page {
  max-width: 600px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  text-align: center;
}

.btn-retry {
  margin-top: 10px;
  padding: 8px 16px;
  background: #c33;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.btn-retry:hover {
  opacity: 0.9;
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
  color: #333;
  font-size: 14px;
}

.form-control {
  padding: 12px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control.error {
  border-color: #c33;
}

.form-control:disabled {
  background: #f5f5f5;
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
  color: #667eea;
  font-size: 18px;
}

.error-text {
  color: #c33;
  font-size: 13px;
  font-weight: 500;
}

.form-hint {
  font-size: 13px;
  color: #666;
}

.link {
  color: #667eea;
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
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover:not(:disabled) {
  background: #e0e0e0;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .expense-form-page {
    padding: 20px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
