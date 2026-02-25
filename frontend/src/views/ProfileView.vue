<template>
  <div class="profile-container">
    <div class="profile-card">
      <PageHeader title="Профіль" back-to="/" />

      <div v-if="authStore.loading" class="loading">Завантаження...</div>

      <div v-else-if="authStore.user" class="user-info">
        <div class="info-row">
          <span class="label">Ім'я:</span>
          <span class="value">{{ authStore.user.name }}</span>
        </div>

        <div class="info-row">
          <span class="label">Email:</span>
          <span class="value">{{ authStore.user.email }}</span>
        </div>

        <div class="info-row">
          <span class="label">Зареєстровано:</span>
          <span class="value">{{ formatDate(authStore.user.created_at) }}</span>
        </div>

        <div class="actions">
          <button @click="handleLogout" class="logout-btn">Вийти</button>
          <router-link to="/" class="back-btn"> На головну </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import PageHeader from '@/components/PageHeader.vue'

const authStore = useAuthStore()

onMounted(async () => {
  // Оновлюємо дані користувача при завантаженні сторінки
  await authStore.fetchUser()
})

function handleLogout() {
  if (confirm('Ви впевнені, що хочете вийти?')) {
    authStore.logout()
  }
}

function formatDate(dateString: string) {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<style scoped>
.profile-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.label {
  font-weight: 600;
  color: #555;
}

.value {
  color: #333;
}

.actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}

.logout-btn,
.back-btn {
  flex: 1;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.3s;
  text-align: center;
  text-decoration: none;
  display: block;
}

.logout-btn {
  background: #e74c3c;
  color: white;
}

.logout-btn:hover {
  opacity: 0.9;
}

.back-btn {
  background: #3498db;
  color: white;
}

.back-btn:hover {
  opacity: 0.9;
}
</style>
