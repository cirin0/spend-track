<template>
  <div class="expenses-container">
    <div class="expenses-page">
      <!-- Header -->
      <PageHeader
        :title="category?.name || 'Витрати категорії'"
        :subtitle="
          category ? (category.is_default ? 'Системна категорія' : 'Моя категорія') : undefined
        "
        :icon="category?.icon || undefined"
        :icon-color="category?.color || undefined"
        back-to="/categories"
      />

      <!-- Loading -->
      <div v-if="loading" class="loading">Завантаження витрат...</div>

      <!-- Error -->
      <div v-else-if="error" class="error-message">
        {{ error }}
        <button @click="loadExpenses" class="btn-retry">Спробувати знову</button>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Stats -->
        <div class="stats-section">
          <div class="stat-card">
            <div class="stat-label">Всього витрат</div>
            <div class="stat-value">{{ expensesData?.count || 0 }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Загальна сума</div>
            <div class="stat-value">{{ formatAmount(expensesData?.total || 0) }} ₴</div>
          </div>
        </div>

        <!-- Expenses List -->
        <div v-if="expensesData && expensesData.data.length > 0" class="expenses-list">
          <h2>Список витрат</h2>
          <div class="expense-card" v-for="expense in expensesData.data" :key="expense.id">
            <div class="expense-header">
              <div class="expense-amount">{{ formatAmount(expense.amount) }} ₴</div>
              <div class="expense-date">{{ formatDate(expense.date) }}</div>
            </div>
            <div class="expense-description">{{ expense.description || 'Без опису' }}</div>
            <div class="expense-footer">
              <span class="expense-id">ID: {{ expense.id }}</span>
              <span class="expense-time">{{ formatDateTime(expense.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">📊</div>
          <h3>Немає витрат</h3>
          <p>У цій категорії ще немає жодної витрати</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { expenseService, type ExpensesByCategoryResponse } from '@/services/expenseService'
import { useCategoryStore } from '@/stores/category'
import PageHeader from '@/components/PageHeader.vue'

const route = useRoute()
const categoryStore = useCategoryStore()

const categorySlug = computed(() => route.params.slug as string)

const expensesData = ref<ExpensesByCategoryResponse | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const category = computed(() => {
  return categoryStore.getCategoryBySlug(categorySlug.value)
})

onMounted(async () => {
  if (!categoryStore.hasCategories) {
    await categoryStore.fetchCategories()
  }

  await loadExpenses()
})

async function loadExpenses() {
  loading.value = true
  error.value = null

  try {
    if (!category.value) {
      error.value = 'Категорію не знайдено'
      return
    }
    expensesData.value = await expenseService.getByCategory(category.value.id)
  } catch (err: unknown) {
    const axiosError = err as { response?: { data?: { message?: string } } }
    error.value = axiosError.response?.data?.message || 'Помилка завантаження витрат'
    console.error('Load expenses error:', err)
  } finally {
    loading.value = false
  }
}

function formatAmount(amount: number): string {
  return amount.toFixed(2)
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatDateTime(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.expenses-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.expenses-page {
  max-width: 1000px;
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
  padding: 20px;
  border-radius: 8px;
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

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 24px;
  border-radius: 12px;
  color: white;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
  margin-bottom: 8px;
  font-weight: 500;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
}

.expenses-list h2 {
  font-size: 22px;
  color: #333;
  margin-bottom: 20px;
}

.expense-card {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
  transition: all 0.3s;
}

.expense-card:hover {
  border-color: #667eea;
  transform: translateX(5px);
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
}

.expense-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.expense-amount {
  font-size: 24px;
  font-weight: 700;
  color: #667eea;
}

.expense-date {
  font-size: 14px;
  color: #666;
  font-weight: 600;
}

.expense-description {
  color: #333;
  font-size: 16px;
  margin-bottom: 12px;
  line-height: 1.5;
}

.expense-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
  font-size: 12px;
  color: #999;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 24px;
  color: #333;
  margin-bottom: 10px;
}

.empty-state p {
  color: #666;
  font-size: 16px;
}

@media (max-width: 768px) {
  .expenses-page {
    padding: 20px;
  }

  .stat-value {
    font-size: 24px;
  }

  .expense-amount {
    font-size: 20px;
  }

  .category-icon {
    width: 50px;
    height: 50px;
    font-size: 24px;
  }
}
</style>
