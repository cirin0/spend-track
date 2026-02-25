<template>
  <div class="category-form-container">
    <div class="category-form-card">
      <PageHeader
        :title="isEditMode ? 'Редагувати категорію' : 'Створити категорію'"
        back-to="/categories"
      />

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
              placeholder="#667eea"
              pattern="^#[0-9A-Fa-f]{6}$"
              maxlength="7"
              class="color-text"
            />
          </div>
          <small>Виберіть колір для іконки категорії</small>
        </div>

        <!-- Preview -->
        <div v-if="form.name || form.icon || form.color" class="preview-section">
          <label>Попередній перегляд:</label>
          <div class="category-preview">
            <div class="preview-icon" :style="{ backgroundColor: form.color || '#e0e0e0' }">
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
          <button type="submit" :disabled="categoryStore.loading || !form.name" class="btn-primary">
            {{ categoryStore.loading ? 'Збереження...' : isEditMode ? 'Зберегти' : 'Створити' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const route = useRoute()
const categoryStore = useCategoryStore()

const isEditMode = computed(() => !!route.params.id)
const categoryId = computed(() => (route.params.id ? Number(route.params.id) : null))

const form = reactive({
  name: '',
  icon: '',
  color: '#667eea',
})

onMounted(async () => {
  if (isEditMode.value && categoryId.value) {
    // Завантажуємо категорії якщо ще не завантажено
    if (!categoryStore.hasCategories) {
      await categoryStore.fetchCategories()
    }

    // Завантажуємо дані категорії для редагування
    const category = categoryStore.getCategoryById(categoryId.value)
    if (category) {
      if (category.is_default) {
        alert('Неможливо редагувати системну категорію')
        router.push('/categories')
        return
      }
      form.name = category.name
      form.icon = category.icon || ''
      form.color = category.color || '#667eea'
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

<style scoped>
.category-form-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-form-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 600px;
}

.form-group {
  margin-bottom: 24px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 600;
  font-size: 14px;
}

input[type='text'] {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

input[type='text']:focus {
  outline: none;
  border-color: #667eea;
}

small {
  display: block;
  margin-top: 6px;
  color: #999;
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
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.3s;
}

.color-picker:hover {
  border-color: #667eea;
}

.color-text {
  flex: 1;
}

.preview-section {
  margin: 30px 0;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
}

.preview-section label {
  margin-bottom: 12px;
}

.category-preview {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: white;
  border-radius: 10px;
  border: 2px solid #e0e0e0;
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
  color: #333;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  text-align: center;
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
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
  text-decoration: none;
  display: block;
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

.btn-secondary:hover {
  background: #e0e0e0;
}

@media (max-width: 768px) {
  .category-form-card {
    padding: 24px;
  }

  .form-header h1 {
    font-size: 24px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
