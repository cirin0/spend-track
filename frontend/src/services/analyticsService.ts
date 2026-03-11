import api from '@/config/api'

export interface CategoryStat {
  category_id: number | null
  category_name: string
  amount: number
  percentage: number
}

export interface PeriodData {
  start_date: string
  end_date: string
  days: number
}

export interface Averages {
  daily: number
  weekly: number
  monthly: number
}

export interface AnalyticsSummary {
  total_amount: number
  period: PeriodData
  averages: Averages
  categories: CategoryStat[]
}

export interface ChartDataPoint {
  label: string
  amount: number
}

export interface AnalyticsCharts {
  monthly: ChartDataPoint[]
  weekly: ChartDataPoint[]
}

function calculatePeriodDates(period: string): { start_date: string; end_date: string } {
  const now = new Date()
  const end_date = now.toISOString().split('T')[0]!
  let start_date: string

  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
  const startOfYear = new Date(now.getFullYear(), 0, 1)

  switch (period) {
    case 'week':
      const weekAgo = new Date(now)
      weekAgo.setDate(now.getDate() - 7)
      start_date = weekAgo.toISOString().split('T')[0]!
      break
    case 'month':
      start_date = startOfMonth.toISOString().split('T')[0]!
      break
    case 'quarter':
      const quarterStart = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1)
      start_date = quarterStart.toISOString().split('T')[0]!
      break
    case 'year':
      start_date = startOfYear.toISOString().split('T')[0]!
      break
    case 'all':
    default:
      start_date = '2020-01-01'
      break
  }

  return { start_date, end_date }
}

export const analyticsService = {
  async getSummary(filters?: {
    period?: string
    start_date?: string
    end_date?: string
  }): Promise<AnalyticsSummary> {
    let params = {}

    if (filters?.start_date && filters?.end_date) {
      params = {
        start_date: filters.start_date,
        end_date: filters.end_date,
      }
    } else if (filters?.period) {
      params = calculatePeriodDates(filters.period)
    }

    const response = await api.get('/analytics/summary', { params })
    return response.data.data
  },

  async getCharts(filters?: {
    period?: string
    start_date?: string
    end_date?: string
  }): Promise<AnalyticsCharts> {
    let params = {}

    if (filters?.start_date && filters?.end_date) {
      params = {
        start_date: filters.start_date,
        end_date: filters.end_date,
      }
    } else if (filters?.period) {
      params = calculatePeriodDates(filters.period)
    }

    const response = await api.get('/analytics/charts', { params })
    return response.data.data
  },
}

export default analyticsService
