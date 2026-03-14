<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const { marginLeft } = useSidebarMargin()

const isEditing = ref(false)
const formData = ref({
  name: '',
  email: '',
})
const avatarFile = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)
const saveMessage = ref('')
const saveError = ref('')

onMounted(async () => {
  await authStore.fetchUser()
  if (authStore.user) {
    formData.value.name = authStore.user.name
    formData.value.email = authStore.user.email
  }
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

function toggleEdit() {
  if (isEditing.value) {
    // Скасувати редагування
    if (authStore.user) {
      formData.value.name = authStore.user.name
      formData.value.email = authStore.user.email
    }
    avatarFile.value = null
    avatarPreview.value = null
    saveMessage.value = ''
    saveError.value = ''
  }
  isEditing.value = !isEditing.value
}

function handleAvatarChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      saveError.value = 'Розмір файлу не може перевищувати 2MB'
      return
    }

    if (!['image/jpeg', 'image/jpg', 'image/png', 'image/gif'].includes(file.type)) {
      saveError.value = 'Дозволені формати: JPEG, JPG, PNG, GIF'
      return
    }

    avatarFile.value = file

    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
    saveError.value = ''
  }
}

function removeAvatar() {
  avatarFile.value = null
  avatarPreview.value = null
  const input = document.getElementById('avatar-input') as HTMLInputElement
  if (input) input.value = ''
}

async function saveProfile() {
  saveMessage.value = ''
  saveError.value = ''

  const updateData: { name?: string; email?: string; avatar?: File } = {}

  if (formData.value.name !== authStore.user?.name) {
    updateData.name = formData.value.name
  }

  if (formData.value.email !== authStore.user?.email) {
    updateData.email = formData.value.email
  }

  if (avatarFile.value) {
    updateData.avatar = avatarFile.value
  }

  if (Object.keys(updateData).length === 0) {
    saveError.value = 'Немає змін для збереження'
    return
  }

  const result = await authStore.updateProfile(updateData)

  if (result.success) {
    saveMessage.value = result.message
    isEditing.value = false
    avatarFile.value = null
    avatarPreview.value = null

    setTimeout(() => {
      saveMessage.value = ''
    }, 3000)
  } else {
    saveError.value = result.message
  }
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
            <!-- Avatar Section -->
            <div class="avatar-section">
              <div class="avatar-container">
                <img
                  v-if="avatarPreview || authStore.user.avatar"
                  :src="(avatarPreview || authStore.user.avatar) ?? undefined"
                  alt="Avatar"
                  class="avatar-image"
                />
                <div v-else class="avatar-placeholder">
                  {{ authStore.user.name.charAt(0).toUpperCase() }}
                </div>
              </div>

              <div v-if="isEditing" class="avatar-controls">
                <label for="avatar-input" class="avatar-upload-btn"> 📷 Завантажити фото </label>
                <input
                  id="avatar-input"
                  type="file"
                  accept="image/jpeg,image/jpg,image/png,image/gif"
                  @change="handleAvatarChange"
                  style="display: none"
                />
                <button
                  v-if="avatarPreview || avatarFile"
                  @click="removeAvatar"
                  class="avatar-remove-btn"
                >
                  ✕ Видалити
                </button>
              </div>
            </div>

            <!-- Messages -->
            <div v-if="saveMessage" class="message success-message">
              {{ saveMessage }}
            </div>
            <div v-if="saveError" class="message error-message">
              {{ saveError }}
            </div>

            <!-- Edit Mode -->
            <div v-if="isEditing" class="edit-form">
              <div class="form-group">
                <label for="name">Ім'я</label>
                <input
                  id="name"
                  v-model="formData.name"
                  type="text"
                  class="form-input"
                  placeholder="Введіть ім'я"
                />
              </div>

              <div class="form-group">
                <label for="email">Email</label>
                <input
                  id="email"
                  v-model="formData.email"
                  type="email"
                  class="form-input"
                  placeholder="Введіть email"
                />
              </div>

              <div class="form-actions">
                <button @click="saveProfile" class="save-btn">💾 Зберегти</button>
                <button @click="toggleEdit" class="cancel-btn">Скасувати</button>
              </div>
            </div>

            <!-- View Mode -->
            <div v-else class="info-rows">
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

              <div class="info-row theme-row">
                <span class="label">Тема оформлення</span>
                <button @click="themeStore.toggleTheme()" class="theme-toggle-btn">
                  {{ themeStore.theme === 'light' ? '🌙 Темна' : '☀️ Світла' }}
                </button>
              </div>

              <div class="actions">
                <button @click="toggleEdit" class="edit-btn">✏️ Редагувати профіль</button>
                <button @click="handleLogout" class="logout-btn">Вийти</button>
                <router-link to="/" class="back-btn">На головну</router-link>
              </div>
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

.avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: var(--bg-secondary);
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.avatar-container {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid var(--primary-color);
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary-color);
  color: white;
  font-size: 48px;
  font-weight: 700;
}

.avatar-controls {
  display: flex;
  gap: 12px;
}

.avatar-upload-btn,
.avatar-remove-btn {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--hover-bg);
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.avatar-upload-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.avatar-remove-btn {
  background: var(--danger-color);
  color: white;
  border-color: var(--danger-color);
}

.avatar-remove-btn:hover {
  opacity: 0.9;
}

.message {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
}

.success-message {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 700;
  color: var(--text-secondary);
  font-size: 14px;
}

.form-input {
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 15px;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.save-btn,
.cancel-btn {
  flex: 1;
  padding: 14px;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.save-btn {
  background: var(--primary-color);
  color: white;
}

.save-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.cancel-btn {
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.cancel-btn:hover {
  background: var(--hover-bg);
}

.info-rows {
  display: flex;
  flex-direction: column;
  gap: 20px;
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

.theme-toggle-btn {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--hover-bg);
  color: var(--text-primary);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.theme-toggle-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.actions {
  margin-top: 20px;
  display: flex;
  gap: 12px;
}

.edit-btn,
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

.edit-btn {
  background: var(--primary-color);
  color: white;
}

.edit-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
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
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.back-btn:hover {
  background: var(--hover-bg);
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

  .actions,
  .form-actions {
    flex-direction: column;
  }

  .avatar-container {
    width: 100px;
    height: 100px;
  }

  .avatar-placeholder {
    font-size: 40px;
  }
}
</style>
