<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { expenseService, type ExpensesByCategoryResponse } from '@/services/expenseService'
import { useCategoryStore } from '@/stores/category'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const route = useRoute()
const categoryStore = useCategoryStore()
const { marginLeft } = useSidebarMargin()

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

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
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
  max-width: 1200px;
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
  padding: 20px;
  border-radius: 12px;
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

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
  padding: 24px;
  border-radius: 12px;
  color: white;
}

[data-theme='light'] .stat-card {
  background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
  margin-bottom: 8px;
  font-weight: 600;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
}

.expenses-list h2 {
  font-size: 22px;
  color: var(--text-primary);
  margin-bottom: 20px;
  font-weight: 700;
}

.expense-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
  transition: all 0.2s;
}

.expense-card:hover {
  border-color: var(--primary-color);
  transform: translateX(5px);
  box-shadow: var(--shadow);
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
  color: var(--primary-color);
}

.expense-date {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 600;
}

.expense-description {
  color: var(--text-primary);
  font-size: 16px;
  margin-bottom: 12px;
  line-height: 1.5;
}

.expense-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
  font-size: 12px;
  color: var(--text-secondary);
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
  color: var(--text-primary);
  margin-bottom: 10px;
}

.empty-state p {
  color: var(--text-secondary);
  font-size: 16px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .stat-value {
    font-size: 24px;
  }

  .expense-amount {
    font-size: 20px;
  }
}
</style>
