<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const route = useRoute()
const groupStore = useGroupStore()
const { marginLeft } = useSidebarMargin()

const isEdit = computed(() => !!route.params.id)
const groupId = computed(() => (route.params.id ? Number(route.params.id) : null))

const formLoading = ref(false)

const form = ref({
  name: '',
  description: '',
  icon: '👥',
  color: '#2563eb',
})

const errors = ref<Record<string, string>>({})

onMounted(async () => {
  if (isEdit.value && groupId.value) {
    await loadGroupData()
  }
})

async function loadGroupData() {
  if (!groupId.value) return

  const success = await groupStore.fetchGroupById(groupId.value)
  if (success && groupStore.currentGroup) {
    form.value = {
      name: groupStore.currentGroup.name,
      description: groupStore.currentGroup.description || '',
      icon: groupStore.currentGroup.icon || '👥',
      color: groupStore.currentGroup.color || '#2563eb',
    }
  }
}

async function handleSubmit() {
  errors.value = {}
  groupStore.clearError()

  if (!form.value.name.trim()) {
    errors.value.name = 'Введіть назву групи'
    return
  }

  formLoading.value = true

  try {
    const data = {
      name: form.value.name.trim(),
      description: form.value.description.trim() || undefined,
      icon: form.value.icon,
      color: form.value.color,
    }

    let result = null

    if (isEdit.value && groupId.value) {
      result = await groupStore.updateGroup(groupId.value, data)
    } else {
      result = await groupStore.createGroup(data)
    }

    if (result) {
      router.push('/groups')
    }
  } catch (error: unknown) {
    console.error('Form submission error:', error)
  } finally {
    formLoading.value = false
  }
}
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader :title="isEdit ? 'Редагувати групу' : 'Створити групу'" back-to="/groups" />

        <div class="form-card">
          <form @submit.prevent="handleSubmit" class="group-form">
            <div class="form-group">
              <label for="name">Назва *</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="form-control"
                :class="{ error: errors.name }"
                placeholder="Наприклад: Сім'я, Друзі, Колеги"
                maxlength="100"
                :disabled="formLoading"
              />
              <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
            </div>

            <div class="form-group">
              <label for="description">Опис</label>
              <textarea
                id="description"
                v-model="form.description"
                class="form-control"
                :class="{ error: errors.description }"
                placeholder="Необов'язково: додайте опис групи"
                rows="3"
                maxlength="500"
                :disabled="formLoading"
              ></textarea>
              <span v-if="errors.description" class="error-text">{{ errors.description }}</span>
              <div class="form-hint">{{ form.description.length }}/500 символів</div>
            </div>

            <div class="form-group">
              <label for="icon">Іконка</label>
              <input
                id="icon"
                v-model="form.icon"
                type="text"
                class="form-control"
                placeholder="Наприклад: 🎉"
                maxlength="10"
                :disabled="formLoading"
              />
              <span v-if="errors.icon" class="error-text">{{ errors.icon }}</span>
            </div>

            <div class="form-group">
              <label for="color">Колір</label>
              <div class="color-input-group">
                <input
                  id="color"
                  v-model="form.color"
                  type="color"
                  class="color-picker-input"
                  :disabled="formLoading"
                />
                <input
                  v-model="form.color"
                  type="text"
                  class="form-control color-text-input"
                  placeholder="#2563eb"
                  pattern="^#[0-9A-Fa-f]{6}$"
                  maxlength="7"
                  :disabled="formLoading"
                />
              </div>
              <span v-if="errors.color" class="error-text">{{ errors.color }}</span>
            </div>

            <div v-if="groupStore.error" class="error-message">
              {{ groupStore.error }}
            </div>

            <div class="form-actions">
              <button
                type="button"
                @click="router.push('/groups')"
                class="btn-secondary"
                :disabled="formLoading"
              >
                Скасувати
              </button>
              <button type="submit" class="btn-primary" :disabled="formLoading">
                <span v-if="formLoading">...</span>
                <span v-else>{{ isEdit ? 'Зберегти' : 'Створити групу' }}</span>
              </button>
            </div>
          </form>
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
  max-width: 900px;
  margin: 0 auto;
  padding: 40px;
}

.form-card {
  background: var(--card-bg);
  padding: 32px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.group-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

label {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.form-control {
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.2s;
  font-family: inherit;
  background: var(--input-bg);
  color: var(--text-primary);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

.form-control.error {
  border-color: var(--danger-color);
}

.form-control:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}

.error-text {
  color: var(--danger-color);
  font-size: 13px;
  font-weight: 500;
}

.form-hint {
  font-size: 13px;
  color: var(--text-secondary);
}

.color-input-group {
  display: flex;
  gap: 12px;
  align-items: center;
}

.color-picker-input {
  width: 60px;
  height: 48px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.2s;
  background: var(--input-bg);
  flex-shrink: 0;
}

.color-picker-input:hover {
  border-color: var(--primary-color);
}

.color-text-input {
  flex: 1;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 16px;
  border-radius: 12px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 14px 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--secondary-bg);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--hover-bg);
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .form-card {
    padding: 20px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
