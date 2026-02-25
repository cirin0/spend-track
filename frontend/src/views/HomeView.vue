<template>
  <div class="home-container">
    <div class="home-card">
      <h1>Spend Track</h1>
      <p class="welcome">Вітаємо, {{ authStore.user?.name }}!</p>

      <!-- Stats Section -->
      <div v-if="expenseStore.stats" class="stats-section">
        <h2>Статистика витрат</h2>

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
                  :style="{ backgroundColor: stat.category?.color || '#e0e0e0' }"
                >
                  {{ stat.category?.icon || '📁' }}
                </div>
                <span class="category-name">{{ stat.category?.name || 'Без категорії' }}</span>
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
                  backgroundColor: stat.category?.color || '#667eea',
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

      <div class="menu">
        <router-link to="/profile" class="menu-item">
          <div class="icon">👤</div>
          <div class="title">Профіль</div>
        </router-link>

        <router-link to="/expenses" class="menu-item">
          <div class="icon">📊</div>
          <div class="title">Витрати</div>
        </router-link>

        <router-link to="/categories" class="menu-item">
          <div class="icon">📁</div>
          <div class="title">Категорії</div>
        </router-link>

        <router-link to="/analytics" class="menu-item">
          <div class="icon">📈</div>
          <div class="title">Аналітика</div>
        </router-link>

        <router-link to="/groups" class="menu-item">
          <div class="icon">👥</div>
          <div class="title">Групи</div>
        </router-link>

        <button @click="handleLogout" class="menu-item logout">
          <div class="icon">🚪</div>
          <div class="title">Вийти</div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useExpenseStore } from '@/stores/expense'

const authStore = useAuthStore()
const expenseStore = useExpenseStore()

onMounted(async () => {
  await expenseStore.fetchStats()
})

function handleLogout() {
  if (confirm('Ви впевнені, що хочете вийти?')) {
    authStore.logout()
  }
}

function formatAmount(amount: number | string): string {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  return num.toFixed(2)
}
</script>

<style scoped>
.home-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.home-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 800px;
}

h1 {
  margin-bottom: 10px;
  text-align: center;
  color: #333;
  font-size: 32px;
}

.welcome {
  text-align: center;
  color: #666;
  font-size: 18px;
  margin-bottom: 30px;
}

.stats-section {
  background: #f8f9fa;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 30px;
}

.stats-section h2 {
  font-size: 20px;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.total-amount {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  margin-bottom: 20px;
  color: white;
}

.total-amount .label {
  font-size: 16px;
  font-weight: 600;
}

.total-amount .amount {
  font-size: 24px;
  font-weight: 700;
}

.category-stats {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-item {
  background: white;
  padding: 16px;
  border-radius: 10px;
  border: 1px solid #e0e0e0;
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
  color: #333;
}

.stat-values {
  display: flex;
  align-items: center;
  gap: 12px;
}

.stat-amount {
  font-size: 18px;
  font-weight: 700;
  color: #667eea;
}

.stat-percentage {
  font-size: 14px;
  font-weight: 600;
  color: #666;
  background: #f0f0f0;
  padding: 4px 8px;
  border-radius: 6px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e0e0e0;
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
  color: #666;
  text-align: right;
}

.no-stats {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.menu {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
}

.menu-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 20px;
  background: #f8f9fa;
  border-radius: 12px;
  text-decoration: none;
  color: #333;
  transition: all 0.3s;
  cursor: pointer;
  border: 2px solid transparent;
  position: relative;
}

.menu-item:not(.disabled):not(.logout):hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  border-color: #667eea;
}

.menu-item.logout {
  background: #fee;
  border: none;
}

.menu-item.logout:hover {
  background: #fdd;
  transform: translateY(-5px);
}

.icon {
  font-size: 48px;
  margin-bottom: 10px;
}

.title {
  font-weight: 600;
  font-size: 16px;
}

@media (max-width: 768px) {
  .home-card {
    padding: 20px;
  }

  .stats-section {
    padding: 16px;
  }

  .total-amount .amount {
    font-size: 20px;
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

  .menu {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
