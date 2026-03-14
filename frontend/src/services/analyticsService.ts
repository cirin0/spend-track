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

export interface AnalyticsFilters {
  from?: string
  to?: string
  category?: number
}

function formatDateParam(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

export function calculatePeriodDates(period: string): { from: string; to: string } {
  const now = new Date()
  const to = formatDateParam(now)
  let from: string

  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
  const startOfYear = new Date(now.getFullYear(), 0, 1)

  switch (period) {
    case 'week':
      const weekAgo = new Date(now)
      weekAgo.setDate(now.getDate() - 7)
      from = formatDateParam(weekAgo)
      break
    case 'month':
      from = formatDateParam(startOfMonth)
      break
    case 'quarter':
      const quarterStart = new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1)
      from = formatDateParam(quarterStart)
      break
    case 'year':
      from = formatDateParam(startOfYear)
      break
    case 'all':
    default:
      from = '2020-01-01'
      break
  }

  return { from, to }
}

function buildAnalyticsParams(filters?: AnalyticsFilters): Record<string, number | string> {
  const params: Record<string, number | string> = {}

  if (filters?.from) {
    params.from = filters.from
  }

  if (filters?.to) {
    params.to = filters.to
  }

  if (filters?.category !== undefined) {
    params.category = filters.category
  }

  return params
}

export const analyticsService = {
  async getSummary(filters?: AnalyticsFilters): Promise<AnalyticsSummary> {
    const params = buildAnalyticsParams(filters)
    const response = await api.get('/analytics/summary', { params })
    return response.data.data
  },

  async getCharts(filters?: AnalyticsFilters): Promise<AnalyticsCharts> {
    const params = buildAnalyticsParams(filters)
    const response = await api.get('/analytics/charts', { params })
    return response.data.data
  },
}

export default analyticsService
