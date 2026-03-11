import api from '@/config/api'

export interface GroupMember {
  id: number
  name: string
  email: string
  role: string
  joined_at: string
}

export interface Group {
  id: number
  name: string
  description: string | null
  icon: string | null
  color: string | null
  owner: {
    id: number
    name: string
    email: string
  }
  members_count: number
  members?: GroupMember[]
  created_at: string
  updated_at: string
}

export interface GroupCategory {
  id: number
  group_id: number
  name: string
  icon: string | null
  color: string | null
  created_at: string
  updated_at: string
}

export interface GroupExpense {
  id: number
  group_id: number
  category_id: number
  amount: number
  description: string | null
  date: string
  user: {
    id: number
    name: string
    email: string
  }
  category: GroupCategory
  created_at: string
  updated_at: string
}

export interface CreateGroupData {
  name: string
  description?: string
  icon?: string
  color?: string
}

export interface UpdateGroupData {
  name?: string
  description?: string
  icon?: string
  color?: string
}

export interface CreateGroupCategoryData {
  name: string
  icon?: string
  color?: string
}

export interface UpdateGroupCategoryData {
  name?: string
  icon?: string
  color?: string
}

export interface CreateGroupExpenseData {
  category_id: number
  amount: number
  description?: string
  date: string
}

export interface UpdateGroupExpenseData {
  category_id?: number
  amount?: number
  description?: string
  date?: string
}

export interface GroupExpenseStatsResponse {
  stats: {
    category: GroupCategory
    total: number
    count: number
    percentage: number
  }[]
  total: number
}

export const groupService = {
  async getAll(): Promise<Group[]> {
    const response = await api.get<{ data: Group[] }>('/groups/')
    return response.data.data
  },

  async getById(id: number): Promise<Group> {
    const response = await api.get<Group>(`/groups/${id}`)
    return response.data
  },

  async create(data: CreateGroupData): Promise<Group> {
    const response = await api.post<Group>('/groups/', data)
    return response.data
  },

  async update(id: number, data: UpdateGroupData): Promise<Group> {
    const response = await api.patch<Group>(`/groups/${id}`, data)
    return response.data
  },

  async delete(id: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(`/groups/${id}`)
    return response.data
  },

  async addMember(groupId: number, userId: number): Promise<Group> {
    const response = await api.post<Group>(`/groups/${groupId}/members`, { user_id: userId })
    return response.data
  },

  async removeMember(groupId: number, userId: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(`/groups/${groupId}/members/${userId}`)
    return response.data
  },

  async leave(groupId: number): Promise<{ message: string }> {
    const response = await api.post<{ message: string }>(`/groups/${groupId}/leave`)
    return response.data
  },

  async getCategories(groupId: number): Promise<GroupCategory[]> {
    const response = await api.get<{ data: GroupCategory[] }>(`/groups/${groupId}/categories`)
    return response.data.data
  },

  async createCategory(groupId: number, data: CreateGroupCategoryData): Promise<GroupCategory> {
    const response = await api.post<GroupCategory>(`/groups/${groupId}/categories`, data)
    return response.data
  },

  async updateCategory(
    groupId: number,
    categoryId: number,
    data: UpdateGroupCategoryData,
  ): Promise<GroupCategory> {
    const response = await api.patch<GroupCategory>(
      `/groups/${groupId}/categories/${categoryId}`,
      data,
    )
    return response.data
  },

  async deleteCategory(groupId: number, categoryId: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(
      `/groups/${groupId}/categories/${categoryId}`,
    )
    return response.data
  },

  async getExpenses(groupId: number): Promise<GroupExpense[]> {
    const response = await api.get<{ data: GroupExpense[] }>(`/groups/${groupId}/expenses`)
    return response.data.data
  },

  async getExpenseStats(groupId: number): Promise<GroupExpenseStatsResponse> {
    const response = await api.get<GroupExpenseStatsResponse>(`/groups/${groupId}/expenses/stats`)
    return response.data
  },

  async createExpense(groupId: number, data: CreateGroupExpenseData): Promise<GroupExpense> {
    const response = await api.post<GroupExpense>(`/groups/${groupId}/expenses`, data)
    return response.data
  },

  async updateExpense(
    groupId: number,
    expenseId: number,
    data: UpdateGroupExpenseData,
  ): Promise<GroupExpense> {
    const response = await api.patch<GroupExpense>(`/groups/${groupId}/expenses/${expenseId}`, data)
    return response.data
  },

  async deleteExpense(groupId: number, expenseId: number): Promise<{ message: string }> {
    const response = await api.delete<{ message: string }>(
      `/groups/${groupId}/expenses/${expenseId}`,
    )
    return response.data
  },
}
