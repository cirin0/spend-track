<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const authStore = useAuthStore()
const { marginLeft } = useSidebarMargin()

onMounted(async () => {
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

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
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
              <router-link to="/" class="back-btn">На головну</router-link>
            </div>
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
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
}

.profile-card {
  background: var(--card-bg);
  padding: 40px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.loading {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-secondary);
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 20px;
  background: var(--bg-secondary);
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.label {
  font-weight: 700;
  color: var(--text-secondary);
  font-size: 14px;
}

.value {
  color: var(--text-primary);
  font-weight: 600;
  font-size: 15px;
}

.actions {
  margin-top: 20px;
  display: flex;
  gap: 12px;
}

.logout-btn,
.back-btn {
  flex: 1;
  padding: 16px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  text-decoration: none;
  display: block;
}

.logout-btn {
  background: var(--danger-color);
  color: white;
}

.logout-btn:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.back-btn {
  background: var(--primary-color);
  color: white;
}

.back-btn:hover {
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

  .profile-card {
    padding: 24px;
  }

  .info-row {
    flex-direction: column;
    gap: 8px;
  }

  .actions {
    flex-direction: column;
  }
}
</style>
