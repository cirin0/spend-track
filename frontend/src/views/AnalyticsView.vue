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
import PageHeader from '@/components/PageHeader.vue'

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
    return { icon: '📁', color: '#e0e0e0' }
  }
  const cat = categories.value.get(categoryId)
  return {
    icon: cat?.icon || '📁',
    color: cat?.color || '#e0e0e0',
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
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
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
  <div class="analytics-container">
    <div class="analytics-page">
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
              <Pie v-if="categoryChartData" :data="categoryChartData" :options="pieChartOptions" />
            </div>
          </div>

          <div class="chart-card wide">
            <h3>Тренд витрат за місяцями</h3>
            <div class="chart-wrapper">
              <Line v-if="monthlyTrendData" :data="monthlyTrendData" :options="lineChartOptions" />
            </div>
          </div>

          <div class="chart-card wide">
            <h3>Топ категорій</h3>
            <div class="chart-wrapper">
              <Bar v-if="topCategoriesData" :data="topCategoriesData" :options="barChartOptions" />
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
    </div>
  </div>
</template>

<style scoped>
.analytics-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1rem;
}

.analytics-page {
  max-width: 1200px;
  margin: 0 auto;
}

.period-select {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  background: white;
  font-size: 0.9rem;
  cursor: pointer;
  font-weight: 500;
}

.loading {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
  font-size: 1.2rem;
}

.error-message {
  background: rgba(255, 255, 255, 0.95);
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  color: #dc3545;
  margin-bottom: 2rem;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  background: #5568d3;
  transform: translateY(-2px);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-label {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 700;
  color: #333;
}

.stat-value-small {
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-card.wide {
  grid-column: 1 / -1;
}

.chart-card h3 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 1.1rem;
  font-weight: 600;
}

.chart-wrapper {
  position: relative;
  height: 300px;
  max-height: 400px;
}

.category-details {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.category-details h3 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 1.1rem;
  font-weight: 600;
}

.category-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.category-item:hover {
  background: #e9ecef;
  transform: translateX(4px);
}

.category-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.category-icon-small {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.category-name {
  font-weight: 600;
  color: #333;
}

.category-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-amount {
  font-weight: 700;
  color: #667eea;
  font-size: 1.1rem;
}

.category-percentage {
  background: #667eea;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

@media (max-width: 768px) {
  .analytics-container {
    padding: 1rem 0.5rem;
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
    gap: 0.25rem;
  }
}
</style>
