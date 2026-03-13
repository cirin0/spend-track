import api from '@/config/api'

export interface User {
  id: number
  name: string
  email: string
  avatar?: string | null
  email_verified_at?: string | null
  created_at: string
  updated_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  expires_at: string
  user: User
}

export interface RegisterResponse {
  message: string
  user: User
}

export interface UpdateProfileData {
  name?: string
  email?: string
  avatar?: File
}

export const authService = {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await api.post<LoginResponse>('/auth/login', credentials)
    return response.data
  },

  async register(data: RegisterData): Promise<RegisterResponse> {
    const response = await api.post<RegisterResponse>('/auth/register', data)
    return response.data
  },

  async me(): Promise<User> {
    const response = await api.get<User>('/auth/me')
    return response.data
  },

  async logout(): Promise<{ message: string }> {
    const response = await api.post<{ message: string }>('/auth/logout')
    return response.data
  },

  async updateProfile(data: UpdateProfileData): Promise<{ message: string; user: User }> {
    const formData = new FormData()

    if (data.name) formData.append('name', data.name)
    if (data.email) formData.append('email', data.email)
    if (data.avatar) formData.append('avatar', data.avatar)

    const response = await api.post<{ message: string; user: User }>('/auth/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    return response.data
  },
}
