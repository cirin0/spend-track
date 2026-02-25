<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import type { Expense } from '@/services/expenseService'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const expenseStore = useExpenseStore()
const { marginLeft } = useSidebarMargin()

onMounted(async () => {
  await expenseStore.fetchExpenses()
})

function editExpense(id: number) {
  router.push(`/expenses/${id}/edit`)
}

async function confirmDelete(expense: Expense) {
  const categoryName = expense.category?.name || 'Без категорії'
  if (
    confirm(`Ви впевнені, що хочете видалити витрату на ${expense.amount} ₴ (${categoryName})?`)
  ) {
    const success = await expenseStore.deleteExpense(expense.id)
    if (success) {
      alert('Витрату успішно видалено')
    }
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
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader title="Витрати" back-to="/">
          <template #actions>
            <router-link to="/expenses/new" class="btn-primary">+ Додати витрату</router-link>
          </template>
        </PageHeader>

        <!-- Loading -->
        <div v-if="expenseStore.loading && !expenseStore.hasExpenses" class="loading">
          Завантаження...
        </div>

        <!-- Error -->
        <div v-else-if="expenseStore.error" class="error-message">
          {{ expenseStore.error }}
          <button @click="expenseStore.fetchExpenses()" class="btn-retry">Спробувати знову</button>
        </div>

        <!-- Content -->
        <div v-else>
          <!-- Stats -->
          <div v-if="expenseStore.hasExpenses" class="stats-section">
            <div class="stat-card">
              <div class="stat-label">Всього витрат</div>
              <div class="stat-value">{{ expenseStore.expenses.length }}</div>
            </div>
            <div class="stat-card">
              <div class="stat-label">Загальна сума</div>
              <div class="stat-value">{{ formatAmount(expenseStore.totalAmount) }} ₴</div>
            </div>
          </div>

          <!-- Expenses List -->
          <div v-if="expenseStore.hasExpenses" class="expenses-list">
            <div class="expense-card" v-for="expense in expenseStore.expenses" :key="expense.id">
              <div class="expense-main">
                <div class="expense-category">
                  <div
                    class="category-icon-small"
                    :style="{ backgroundColor: expense.category?.color || '#6b7280' }"
                  >
                    {{ expense.category?.icon || '📁' }}
                  </div>
                  <div class="category-name">{{ expense.category?.name || 'Без категорії' }}</div>
                </div>

                <div class="expense-details">
                  <div class="expense-header">
                    <div class="expense-amount">{{ formatAmount(expense.amount) }} ₴</div>
                    <div class="expense-date">{{ formatDate(expense.date) }}</div>
                  </div>
                  <div class="expense-description">{{ expense.description || 'Без опису' }}</div>
                </div>
              </div>

              <div class="expense-actions">
                <button @click="editExpense(expense.id)" class="btn-icon" title="Редагувати">
                  ✏️
                </button>
                <button @click="confirmDelete(expense)" class="btn-icon delete" title="Видалити">
                  🗑️
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="empty-state">
            <div class="empty-icon">💸</div>
            <h3>Немає витрат</h3>
            <p>Почніть додавати свої витрати для відстеження бюджету</p>
            <router-link to="/expenses/new" class="btn-primary">Додати першу витрату</router-link>
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

.btn-primary {
  padding: 12px 24px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 700;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: var(--primary-color);
  color: white;
  display: inline-block;
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
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
  transition: opacity 0.2s;
}

.btn-retry:hover {
  opacity: 0.9;
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
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

.expenses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.expense-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 15px;
  transition: all 0.2s;
}

.expense-card:hover {
  border-color: var(--primary-color);
  transform: translateX(5px);
  box-shadow: var(--shadow);
}

.expense-main {
  flex: 1;
  display: flex;
  gap: 15px;
  align-items: center;
  min-width: 0;
}

.expense-category {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
}

.category-icon-small {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.category-name {
  font-size: 11px;
  color: var(--text-secondary);
  text-align: center;
  max-width: 80px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.expense-details {
  flex: 1;
  min-width: 0;
}

.expense-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  gap: 15px;
}

.expense-amount {
  font-size: 24px;
  font-weight: 700;
  color: var(--primary-color);
  white-space: nowrap;
}

.expense-date {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 600;
  white-space: nowrap;
}

.expense-description {
  color: var(--text-primary);
  font-size: 16px;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.expense-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  background: var(--hover-bg);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: var(--secondary-bg);
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  background: rgba(239, 68, 68, 0.1);
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
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .expense-main {
    flex-direction: column;
    align-items: flex-start;
  }

  .expense-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }

  .expense-amount {
    font-size: 20px;
  }

  .stat-value {
    font-size: 24px;
  }
}
</style>
