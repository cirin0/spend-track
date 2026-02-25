<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
} from 'chart.js'
import { Pie, Line, Bar } from 'vue-chartjs'
import analyticsService, {
  type AnalyticsSummary,
  type AnalyticsCharts,
} from '@/services/analyticsService'
import { categoryService, type Category } from '@/services/categoryService'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const { marginLeft } = useSidebarMargin()

ChartJS.register(
  ArcElement,
  Tooltip,
  Legend,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
)

const loading = ref(false)
const error = ref('')
const selectedPeriod = ref('month')
const summary = ref<AnalyticsSummary | null>(null)
const charts = ref<AnalyticsCharts | null>(null)
const categories = ref<Map<number, Category>>(new Map())

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'bottom' as const,
    },
    tooltip: {
      callbacks: {
        label: function (context: unknown) {
          const ctx = context as { label?: string; parsed?: number }
          const label = ctx.label || ''
          const value = ctx.parsed || 0
          return `${label}: ${value.toFixed(2)} ₴`
        },
      },
    },
  },
}

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      callbacks: {
        label: function (context: unknown) {
          const ctx = context as { parsed: { y: number } }
          return `${ctx.parsed.y.toFixed(2)} ₴`
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function (value: unknown) {
          return value + ' ₴'
        },
      },
    },
  },
}

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  indexAxis: 'y' as const,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      callbacks: {
        label: function (context: unknown) {
          const ctx = context as { parsed: { x: number } }
          return `${ctx.parsed.x.toFixed(2)} ₴`
        },
      },
    },
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        callback: function (value: unknown) {
          return value + ' ₴'
        },
      },
    },
  },
}

const getCategoryDetails = (categoryId: number | null) => {
  if (!categoryId) {
    return { icon: '📁', color: '#6b7280' }
  }
  const cat = categories.value.get(categoryId)
  return {
    icon: cat?.icon || '📁',
    color: cat?.color || '#6b7280',
  }
}

const categoryChartData = computed(() => {
  if (!summary.value?.categories?.length) return null

  const top5 = summary.value.categories.slice(0, 5)

  return {
    labels: top5.map((cat) => cat.category_name),
    datasets: [
      {
        data: top5.map((cat) => cat.amount),
        backgroundColor: top5.map((cat) => getCategoryDetails(cat.category_id).color),
        borderWidth: 1,
      },
    ],
  }
})

const monthlyTrendData = computed(() => {
  if (!charts.value?.monthly?.length) return null

  return {
    labels: charts.value.monthly.map((item) => item.label),
    datasets: [
      {
        label: 'Витрати',
        data: charts.value.monthly.map((item) => item.amount),
        borderColor: 'rgb(37, 99, 235)',
        backgroundColor: 'rgba(37, 99, 235, 0.2)',
        tension: 0.1,
      },
    ],
  }
})

const topCategoriesData = computed(() => {
  if (!summary.value?.categories?.length) return null

  const top10 = summary.value.categories.slice(0, 10)

  return {
    labels: top10.map((cat) => cat.category_name),
    datasets: [
      {
        label: 'Витрати',
        data: top10.map((cat) => cat.amount),
        backgroundColor: top10.map((cat) => getCategoryDetails(cat.category_id).color),
        borderWidth: 1,
      },
    ],
  }
})

const formatAmount = (amount: number): string => {
  if (amount === null || amount === undefined || isNaN(amount)) return '0.00'
  return new Intl.NumberFormat('uk-UA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDate = (dateString: string): string => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return new Intl.DateTimeFormat('uk-UA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
}

const loadCategories = async () => {
  try {
    const response = await categoryService.getAll()
    const allCategories = [...response.default, ...response.user]
    allCategories.forEach((cat) => {
      categories.value.set(cat.id, cat)
    })
  } catch (err) {
    console.error('Failed to load categories:', err)
  }
}

const loadData = async () => {
  loading.value = true
  error.value = ''

  try {
    const [summaryData, chartsData] = await Promise.all([
      analyticsService.getSummary({ period: selectedPeriod.value }),
      analyticsService.getCharts({ period: selectedPeriod.value }),
    ])
    summary.value = summaryData
    charts.value = chartsData
  } catch (err: unknown) {
    const errorObj = err as { response?: { data?: { message?: string } } }
    error.value = errorObj.response?.data?.message || 'Помилка завантаження даних'
    console.error('Analytics error:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadCategories()
  await loadData()
})
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader title="Аналітика" back-to="/">
          <template #actions>
            <select v-model="selectedPeriod" @change="loadData" class="period-select">
              <option value="week">Тиждень</option>
              <option value="month">Місяць</option>
              <option value="quarter">Квартал</option>
              <option value="year">Рік</option>
              <option value="all">Весь час</option>
            </select>
          </template>
        </PageHeader>

        <div v-if="loading" class="loading">Завантаження...</div>

        <div v-else-if="error" class="error-message">
          {{ error }}
          <button @click="loadData" class="btn-retry">Спробувати знову</button>
        </div>

        <div v-else-if="summary">
          <div v-if="summary.categories && summary.categories.length > 0">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-label">Всього категорій</div>
                <div class="stat-value">{{ summary.categories?.length || 0 }}</div>
              </div>
              <div class="stat-card">
                <div class="stat-label">Загальна сума</div>
                <div class="stat-value">{{ formatAmount(summary.total_amount) }} ₴</div>
              </div>
              <div class="stat-card">
                <div class="stat-label">Середньо на день</div>
                <div class="stat-value">{{ formatAmount(summary.averages.daily) }} ₴</div>
              </div>
              <div class="stat-card">
                <div class="stat-label">Період</div>
                <div class="stat-value-small">
                  {{ formatDate(summary.period.start_date) }} -
                  {{ formatDate(summary.period.end_date) }}
                </div>
              </div>
            </div>

            <div class="charts-section">
              <div class="chart-card">
                <h3>Витрати за категоріями</h3>
                <div class="chart-wrapper">
                  <Pie
                    v-if="categoryChartData"
                    :data="categoryChartData"
                    :options="pieChartOptions"
                  />
                </div>
              </div>

              <div class="chart-card wide">
                <h3>Тренд витрат за місяцями</h3>
                <div class="chart-wrapper">
                  <Line
                    v-if="monthlyTrendData"
                    :data="monthlyTrendData"
                    :options="lineChartOptions"
                  />
                </div>
              </div>

              <div class="chart-card wide">
                <h3>Топ категорій</h3>
                <div class="chart-wrapper">
                  <Bar
                    v-if="topCategoriesData"
                    :data="topCategoriesData"
                    :options="barChartOptions"
                  />
                </div>
              </div>
            </div>

            <div class="category-details">
              <h3>Детальна статистика за категоріями</h3>
              <div class="category-list">
                <div
                  v-for="cat in summary.categories"
                  :key="cat.category_id || 'uncategorized'"
                  class="category-item"
                >
                  <div class="category-info">
                    <div
                      class="category-icon-small"
                      :style="{ backgroundColor: getCategoryDetails(cat.category_id).color }"
                    >
                      {{ getCategoryDetails(cat.category_id).icon }}
                    </div>
                    <div class="category-name">{{ cat.category_name }}</div>
                  </div>
                  <div class="category-stats">
                    <div class="category-amount">{{ formatAmount(cat.amount) }} ₴</div>
                    <div class="category-percentage">{{ cat.percentage.toFixed(1) }}%</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <div class="empty-icon">📊</div>
            <h3>Немає даних для аналітики</h3>
            <p>Додайте витрати, щоб побачити статистику та графіки</p>
            <router-link to="/expenses/new" class="btn-primary">Додати витрату</router-link>
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

.period-select {
  padding: 12px 20px;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--input-bg);
  color: var(--text-primary);
  font-size: 14px;
  cursor: pointer;
  font-weight: 600;
  transition: border-color 0.2s;
}

.period-select:focus {
  outline: none;
  border-color: var(--primary-color);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
  font-size: 18px;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  color: var(--danger-color);
  margin-bottom: 20px;
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

.stats-grid {
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
  font-size: 28px;
  font-weight: 700;
}

.stat-value-small {
  font-size: 15px;
  font-weight: 600;
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

.chart-card {
  background: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.chart-card.wide {
  grid-column: 1 / -1;
}

.chart-card h3 {
  margin: 0 0 20px 0;
  color: var(--text-primary);
  font-size: 18px;
  font-weight: 700;
}

.chart-wrapper {
  position: relative;
  height: 300px;
  max-height: 400px;
}

.category-details {
  background: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.category-details h3 {
  margin: 0 0 20px 0;
  color: var(--text-primary);
  font-size: 18px;
  font-weight: 700;
}

.category-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: var(--bg-secondary);
  border-radius: 10px;
  transition: all 0.2s;
  border: 1px solid var(--border-color);
}

.category-item:hover {
  transform: translateX(4px);
}

.category-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-icon-small {
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

.category-stats {
  display: flex;
  align-items: center;
  gap: 16px;
}

.category-amount {
  font-weight: 700;
  color: var(--primary-color);
  font-size: 18px;
}

.category-percentage {
  background: var(--primary-color);
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-state .empty-icon {
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

.empty-state .btn-primary {
  display: inline-block;
  padding: 12px 24px;
  background: var(--primary-color);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.2s;
}

.empty-state .btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .charts-section {
    grid-template-columns: 1fr;
  }

  .chart-card.wide {
    grid-column: 1;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .category-stats {
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }
}
</style>
