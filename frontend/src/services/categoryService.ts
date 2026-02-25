import api from '@/config/api'

export interface Category {
  id: number
  name: string
  slug: string
  icon: string | null
  color: string | null
  user_id?: number
  is_default: boolean
  created_at: string
  updated_at: string
}

export interface CategoriesResponse {
  default: Category[]
  user: Category[]
  all: Category[]
}

export interface CreateCategoryData {
  name: string
  icon?: string
  color?: string
}

export interface UpdateCategoryData {
  name?: string
  icon?: string
  color?: string
}

export const categoryService = {
  async getAll(): Promise<CategoriesResponse> {
    const response = await api.get<CategoriesResponse>('/categories')
    return response.data
  },

  async getBySlug(slug: string): Promise<Category> {
    const response = await api.get<Category>(`/categories/${slug}`)
    return response.data
  },

  async create(data: CreateCategoryData): Promise<Category> {
    const response = await api.post<Category>('/categories', data)
    return response.data
  },

  async update(
    id: number,
    data: UpdateCategoryData,
  ): Promise<{ message: string; category: Category }> {
    const response = await api.patch<{ message: string; category: Category }>(
      `/categories/${id}`,
      data,
    )
    return response.data
  },

  async delete(id: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(`/categories/${id}`)
    return response.data
  },
}
