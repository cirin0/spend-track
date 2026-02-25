<template>
  <div class="group-form-container">
    <div class="group-form-page">
      <PageHeader :title="isEdit ? 'Редагувати групу' : 'Створити групу'" back-to="/groups" />

      <form @submit.prevent="handleSubmit" class="group-form">
        <!-- Name -->
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

        <!-- Description -->
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

        <!-- Icon -->
        <div class="form-group">
          <label for="icon">Іконка</label>
          <div class="icon-picker">
            <div
              v-for="emoji in availableIcons"
              :key="emoji"
              class="icon-option"
              :class="{ selected: form.icon === emoji }"
              @click="form.icon = emoji"
            >
              {{ emoji }}
            </div>
          </div>
          <span v-if="errors.icon" class="error-text">{{ errors.icon }}</span>
        </div>

        <!-- Color -->
        <div class="form-group">
          <label for="color">Колір</label>
          <div class="color-picker">
            <div
              v-for="colorOption in availableColors"
              :key="colorOption"
              class="color-option"
              :class="{ selected: form.color === colorOption }"
              :style="{ backgroundColor: colorOption }"
              @click="form.color = colorOption"
            ></div>
          </div>
          <span v-if="errors.color" class="error-text">{{ errors.color }}</span>
        </div>

        <!-- Error Message -->
        <div v-if="groupStore.error" class="error-message">
          {{ groupStore.error }}
        </div>

        <!-- Actions -->
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
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const route = useRoute()
const groupStore = useGroupStore()

const isEdit = computed(() => !!route.params.id)
const groupId = computed(() => (route.params.id ? Number(route.params.id) : null))

const formLoading = ref(false)

const form = ref({
  name: '',
  description: '',
  icon: '👥',
  color: '#667eea',
})

const errors = ref<Record<string, string>>({})

const availableIcons = ['👥', '👨‍👩‍👧‍👦', '🏠', '💼', '🎓', '⚽', '🎨', '🎮', '🍕', '✈️', '💰', '🎯']
const availableColors = [
  '#667eea',
  '#764ba2',
  '#f093fb',
  '#4facfe',
  '#43e97b',
  '#fa709a',
  '#fee140',
  '#30cfd0',
  '#a8edea',
  '#ff6b6b',
]

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
      color: groupStore.currentGroup.color || '#667eea',
    }
  }
}

async function handleSubmit() {
  // Reset errors
  errors.value = {}
  groupStore.clearError()

  // Validate
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

<style scoped>
.group-form-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.group-form-page {
  max-width: 700px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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
  color: #333;
  font-size: 14px;
}

.form-control {
  padding: 12px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control.error {
  border-color: #c33;
}

.form-control:disabled {
  background: #f5f5f5;
  cursor: not-allowed;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
}

.error-text {
  color: #c33;
  font-size: 13px;
  font-weight: 500;
}

.form-hint {
  font-size: 13px;
  color: #666;
}

.icon-picker {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
  gap: 10px;
}

.icon-option {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.icon-option:hover {
  border-color: #667eea;
  transform: scale(1.1);
}

.icon-option.selected {
  border-color: #667eea;
  background: #f0f4ff;
}

.color-picker {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
  gap: 10px;
}

.color-option {
  width: 50px;
  height: 50px;
  border: 3px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.color-option:hover {
  transform: scale(1.1);
}

.color-option.selected {
  border-color: #333;
  box-shadow:
    0 0 0 2px white,
    0 0 0 4px #333;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 16px;
  border-radius: 8px;
  text-align: center;
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
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
  border: none;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f0f0f0;
  color: #333;
}

.btn-secondary:hover:not(:disabled) {
  background: #e0e0e0;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .group-form-page {
    padding: 20px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
