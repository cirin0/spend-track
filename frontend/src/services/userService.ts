import api from '@/config/api'

export interface User {
  id: number
  name: string
  email: string
  avatar?: string | null
  created_at: string
  updated_at: string
}

export const userService = {
  async search(query: string): Promise<User[]> {
    const response = await api.get<{ data: User[] }>('/users/search', {
      params: { query },
    })
    return response.data.data
  },
}
