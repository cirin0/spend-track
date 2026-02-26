<script setup lang="ts">
import { reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const route = useRoute()
const categoryStore = useCategoryStore()
const { marginLeft } = useSidebarMargin()

const isEditMode = computed(() => !!route.params.id)
const categoryId = computed(() => (route.params.id ? Number(route.params.id) : null))

const form = reactive({
  name: '',
  icon: '📁',
  color: '#2563eb',
})

onMounted(async () => {
  if (isEditMode.value && categoryId.value) {
    if (!categoryStore.hasCategories) {
      await categoryStore.fetchCategories()
    }

    const category = categoryStore.getCategoryById(categoryId.value)
    if (category) {
      if (category.is_default) {
        alert('Неможливо редагувати системну категорію')
        router.push('/categories')
        return
      }
      form.name = category.name
      form.icon = category.icon || '📁'
      form.color = category.color || '#2563eb'
    } else {
      alert('Категорію не знайдено')
      router.push('/categories')
    }
  }
})

async function handleSubmit() {
  const data = {
    name: form.name,
    icon: form.icon || undefined,
    color: form.color || undefined,
  }

  let success
  if (isEditMode.value && categoryId.value) {
    const result = await categoryStore.updateCategory(categoryId.value, data)
    success = !!result
  } else {
    const result = await categoryStore.createCategory(data)
    success = !!result
  }

  if (success) {
    router.push('/categories')
  }
}
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader
          :title="isEditMode ? 'Редагувати категорію' : 'Створити категорію'"
          back-to="/categories"
        />

        <div class="form-card">
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label for="name">Назва категорії *</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                placeholder="Наприклад: Продукти"
                maxlength="255"
              />
            </div>

            <div class="form-group">
              <label for="icon">Іконка (emoji)</label>
              <input id="icon" v-model="form.icon" type="text" placeholder="🍕" maxlength="10" />
              <small>Виберіть emoji іконку для категорії</small>
            </div>

            <div class="form-group">
              <label for="color">Колір</label>
              <div class="color-input-group">
                <input id="color" v-model="form.color" type="color" class="color-picker" />
                <input
                  v-model="form.color"
                  type="text"
                  placeholder="#2563eb"
                  pattern="^#[0-9A-Fa-f]{6}$"
                  maxlength="7"
                  class="color-text"
                />
              </div>
              <small>Виберіть колір для іконки категорії</small>
            </div>

            <div v-if="form.name || form.icon || form.color" class="preview-section">
              <label>Попередній перегляд:</label>
              <div class="category-preview">
                <div class="preview-icon" :style="{ backgroundColor: form.color || '#6b7280' }">
                  {{ form.icon || '📁' }}
                </div>
                <span class="preview-name">{{ form.name || 'Назва категорії' }}</span>
              </div>
            </div>

            <div v-if="categoryStore.error" class="error-message">
              {{ categoryStore.error }}
            </div>

            <div class="form-actions">
              <router-link to="/categories" class="btn-secondary">Скасувати</router-link>
              <button
                type="submit"
                :disabled="categoryStore.loading || !form.name"
                class="btn-primary"
              >
                {{ categoryStore.loading ? 'Збереження...' : isEditMode ? 'Зберегти' : 'Створити' }}
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
  max-width: 800px;
  margin: 0 auto;
  padding: 40px;
}

.form-card {
  background: var(--card-bg);
  padding: 32px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.form-group {
  margin-bottom: 24px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-primary);
  font-weight: 600;
  font-size: 14px;
}

input[type='text'] {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.2s;
  box-sizing: border-box;
  background: var(--input-bg);
  color: var(--text-primary);
}

input[type='text']:focus {
  outline: none;
  border-color: var(--primary-color);
}

small {
  display: block;
  margin-top: 6px;
  color: var(--text-secondary);
  font-size: 13px;
}

.color-input-group {
  display: flex;
  gap: 12px;
  align-items: center;
}

.color-picker {
  width: 60px;
  height: 48px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.2s;
  background: var(--input-bg);
}

.color-picker:hover {
  border-color: var(--primary-color);
}

.color-text {
  flex: 1;
}

.preview-section {
  margin: 30px 0;
  padding: 20px;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.preview-section label {
  margin-bottom: 12px;
}

.category-preview {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: var(--card-bg);
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.preview-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.preview-name {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 30px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 14px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  text-decoration: none;
  display: block;
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

.btn-secondary:hover {
  background: var(--hover-bg);
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
