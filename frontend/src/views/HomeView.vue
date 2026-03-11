<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useExpenseStore } from '@/stores/expense'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import AppSidebar from '@/components/AppSidebar.vue'
import ExpenseForm from '@/components/ExpenseForm.vue'
import type { CreateExpenseData } from '@/services/expenseService'

const authStore = useAuthStore()
const expenseStore = useExpenseStore()
const { marginLeft } = useSidebarMargin()

const recentExpenses = computed(() => {
  return expenseStore.expenses.slice(0, 5)
})

onMounted(async () => {
  await Promise.all([expenseStore.fetchStats(), expenseStore.fetchExpenses()])
})

async function handleCreateExpense(data: CreateExpenseData) {
  const result = await expenseStore.createExpense(data)
  if (result) {
    await expenseStore.fetchStats()
  }
}

function resetForm() {}

function formatAmount(amount: number | string): string {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  return num.toFixed(2)
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', { day: 'numeric', month: 'short' })
}
</script>

<template>
  <div class="home-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <div class="header">
          <div>
            <h1>Вітаємо, {{ authStore.user?.name }}!</h1>
            <p class="subtitle">Керуйте своїми витратами ефективно</p>
          </div>
        </div>

        <div class="dashboard-grid">
          <div class="quick-add-section">
            <ExpenseForm
              title="Швидке додавання витрати"
              :loading="expenseStore.loading"
              @submit="handleCreateExpense"
              @cancel="resetForm"
            />
          </div>

          <div class="stats-section">
            <div class="stats-card">
              <h2>Статистика витрат</h2>

              <div v-if="expenseStore.stats" class="stats-content">
                <div class="total-amount">
                  <span class="label">Загальна сума:</span>
                  <span class="amount">{{ formatAmount(expenseStore.stats.total) }} ₴</span>
                </div>

                <div v-if="expenseStore.stats.stats.length > 0" class="category-stats">
                  <div
                    v-for="stat in expenseStore.stats.stats"
                    :key="stat.category ? `category-${stat.category.id}` : 'no-category'"
                    class="stat-item"
                  >
                    <div class="stat-header">
                      <div class="category-info">
                        <div
                          class="category-icon"
                          :style="{ backgroundColor: stat.category?.color || '#6b7280' }"
                        >
                          {{ stat.category?.icon || '📁' }}
                        </div>
                        <span class="category-name">{{
                          stat.category?.name || 'Без категорії'
                        }}</span>
                      </div>
                      <div class="stat-values">
                        <span class="stat-amount">{{ formatAmount(stat.total) }} ₴</span>
                        <span class="stat-percentage">{{ stat.percentage }}%</span>
                      </div>
                    </div>
                    <div class="progress-bar">
                      <div
                        class="progress-fill"
                        :style="{
                          width: stat.percentage + '%',
                          backgroundColor: stat.category?.color || '#3b82f6',
                        }"
                      ></div>
                    </div>
                    <div class="stat-count">
                      {{ stat.count }} {{ stat.count === 1 ? 'витрата' : 'витрат' }}
                    </div>
                  </div>
                </div>

                <div v-else class="no-stats">
                  <p>Ще немає витрат для відображення статистики</p>
                </div>
              </div>

              <div v-else class="loading">Завантаження статистики...</div>
            </div>
          </div>
        </div>

        <div v-if="recentExpenses.length > 0" class="recent-expenses">
          <div class="section-header">
            <h2>Останні витрати</h2>
            <router-link to="/expenses" class="view-all">Переглянути всі →</router-link>
          </div>
          <div class="expenses-list">
            <div v-for="expense in recentExpenses" :key="expense.id" class="expense-item">
              <div
                class="expense-icon"
                :style="{ backgroundColor: expense.category?.color || '#6b7280' }"
              >
                {{ expense.category?.icon || '📁' }}
              </div>
              <div class="expense-details">
                <div class="expense-description">{{ expense.description || 'Без опису' }}</div>
                <div class="expense-meta">
                  {{ expense.category?.name || 'Без категорії' }} • {{ formatDate(expense.date) }}
                </div>
              </div>
              <div class="expense-amount">{{ formatAmount(expense.amount) }} ₴</div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.home-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-primary);
}

.main-content {
  flex: 1;
  transition: margin-left 0.3s ease;
}

.content-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px;
}

.header {
  margin-bottom: 40px;
}

h1 {
  font-size: 32px;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.subtitle {
  color: var(--text-secondary);
  font-size: 16px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 40px;
}

.quick-add-section,
.stats-section {
  min-height: 400px;
}

.stats-card {
  background: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  height: 100%;
}

.stats-card h2 {
  font-size: 20px;
  color: var(--text-primary);
  margin-bottom: 20px;
}

.stats-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.total-amount {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
  border-radius: 10px;
  color: white;
}

[data-theme='light'] .total-amount {
  background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
}

.total-amount .label {
  font-size: 16px;
  font-weight: 600;
}

.total-amount .amount {
  font-size: 28px;
  font-weight: 700;
}

.category-stats {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-item {
  background: var(--bg-secondary);
  padding: 16px;
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.category-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.category-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.category-name {
  font-weight: 600;
  color: var(--text-primary);
}

.stat-values {
  display: flex;
  align-items: center;
  gap: 12px;
}

.stat-amount {
  font-size: 18px;
  font-weight: 700;
  color: var(--primary-color);
}

.stat-percentage {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  background: var(--hover-bg);
  padding: 4px 8px;
  border-radius: 6px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: var(--border-color);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  transition: width 0.3s ease;
  border-radius: 4px;
}

.stat-count {
  font-size: 13px;
  color: var(--text-secondary);
  text-align: right;
}

.no-stats,
.loading {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-secondary);
}

.recent-expenses {
  background: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 20px;
  color: var(--text-primary);
}

.view-all {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: color 0.2s;
}

.view-all:hover {
  color: var(--primary-hover);
}

.expenses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.expense-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: var(--bg-secondary);
  border-radius: 10px;
  border: 1px solid var(--border-color);
  transition: transform 0.2s;
}

.expense-item:hover {
  transform: translateX(4px);
}

.expense-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.expense-details {
  flex: 1;
}

.expense-description {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.expense-meta {
  font-size: 13px;
  color: var(--text-secondary);
}

.expense-amount {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  h1 {
    font-size: 24px;
  }

  .stat-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .stat-values {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
