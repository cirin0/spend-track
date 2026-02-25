<template>
  <div class="expenses-container">
    <div class="expenses-page">
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
                  :style="{ backgroundColor: expense.category?.color || '#e0e0e0' }"
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
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useExpenseStore } from '@/stores/expense'
import type { Expense } from '@/services/expenseService'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const expenseStore = useExpenseStore()

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

.btn-primary {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: inline-block;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-2px);
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
  margin-bottom: 30px;
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

.expenses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.expense-card {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 15px;
  transition: all 0.3s;
}

.expense-card:hover {
  border-color: #667eea;
  transform: translateX(5px);
  box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
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
  color: #666;
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
  color: #667eea;
  white-space: nowrap;
}

.expense-date {
  font-size: 14px;
  color: #666;
  font-weight: 600;
  white-space: nowrap;
}

.expense-description {
  color: #333;
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
  background: #f0f0f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: #e0e0e0;
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  background: #fee;
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
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .expenses-page {
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
