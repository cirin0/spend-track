import api from '@/config/api'

export interface Expense {
  id: number
  amount: number
  currency: string
  converted_amount: number
  exchange_rate: number
  description: string
  date: string
  category: {
    id: number
    name: string | null
    icon: string | null
    color: string | null
    slug: string
    is_default: boolean
  } | null
  created_at: string
  updated_at: string
}

export interface ExpensesByCategoryResponse {
  data: Expense[]
  total: number
  count: number
}

export interface ExpensesListResponse {
  data: Expense[]
  total: number
  count: number
}

export interface CreateExpenseData {
  category_id?: number
  amount: number
  currency: string
  converted_amount: number
  exchange_rate: number
  description?: string
  date: string
}

export interface UpdateExpenseData {
  category_id?: number
  amount?: number
  currency?: string
  converted_amount?: number
  exchange_rate?: number
  description?: string
  date?: string
}

export interface CategoryStat {
  category: {
    id: number
    is_default: boolean
    name: string | null
    icon: string | null
    color: string | null
    slug: string
  } | null
  total: number
  count: number
  percentage: number
}

export interface ExpenseStatsResponse {
  stats: CategoryStat[]
  total: number
}

export const expenseService = {
  async getAll(): Promise<Expense[]> {
    const response = await api.get<ExpensesListResponse>('/expenses')
    return response.data.data
  },

  async getById(id: number): Promise<Expense> {
    const response = await api.get<Expense>(`/expenses/${id}`)
    return response.data
  },

  async getByCategory(categoryId: number): Promise<ExpensesByCategoryResponse> {
    const response = await api.get<ExpensesByCategoryResponse>(`/expenses/category/${categoryId}`)
    return response.data
  },

  async getStats(): Promise<ExpenseStatsResponse> {
    const response = await api.get<ExpenseStatsResponse>('/expenses/stats')
    return response.data
  },

  async create(data: CreateExpenseData): Promise<Expense> {
    const response = await api.post<Expense>('/expenses', data)
    return response.data
  },

  async update(
    id: number,
    data: UpdateExpenseData,
  ): Promise<{ message: string; expense: Expense }> {
    const response = await api.patch<{ message: string; expense: Expense }>(`/expenses/${id}`, data)
    return response.data
  },

  async delete(id: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(`/expenses/${id}`)
    return response.data
  },
}
