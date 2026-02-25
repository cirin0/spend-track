<template>
  <div class="categories-container">
    <div class="categories-page">
      <PageHeader title="Категорії" back-to="/">
        <template #actions>
          <router-link to="/categories/new" class="btn-primary">+ Створити категорію</router-link>
        </template>
      </PageHeader>

      <div v-if="categoryStore.loading && !categoryStore.hasCategories" class="loading">
        Завантаження...
      </div>

      <div v-else-if="categoryStore.error" class="error-message">
        {{ categoryStore.error }}
        <button @click="categoryStore.fetchCategories()" class="btn-retry">Спробувати знову</button>
      </div>

      <div v-else class="categories-content">
        <!-- Дефолтні категорії -->
        <section class="category-section">
          <h2>📦 Системні категорії</h2>
          <p class="section-description">Категорії доступні для всіх користувачів</p>

          <div v-if="categoryStore.defaultCategories.length > 0" class="categories-grid">
            <div
              v-for="category in categoryStore.defaultCategories"
              :key="'default-' + category.id"
              class="category-card default clickable"
              @click="viewCategoryExpenses(category)"
            >
              <div class="category-icon" :style="{ backgroundColor: category.color || '#e0e0e0' }">
                {{ category.icon || '📁' }}
              </div>
              <div class="category-info">
                <h3>{{ category.name }}</h3>
                <span class="category-badge">Системна</span>
              </div>
              <div class="view-icon">→</div>
            </div>
          </div>
          <div v-else class="empty-state">Немає системних категорій</div>
        </section>

        <!-- Користувацькі категорії -->
        <section class="category-section">
          <h2>✨ Мої категорії</h2>
          <p class="section-description">Ваші власні категорії</p>

          <div v-if="categoryStore.userCategories.length > 0" class="categories-grid">
            <div
              v-for="category in categoryStore.userCategories"
              :key="'user-' + category.id"
              class="category-card user clickable"
            >
              <div class="card-content" @click="viewCategoryExpenses(category)">
                <div
                  class="category-icon"
                  :style="{ backgroundColor: category.color || '#e0e0e0' }"
                >
                  {{ category.icon || '📁' }}
                </div>
                <div class="category-info">
                  <h3>{{ category.name }}</h3>
                  <span class="category-badge user">Моя</span>
                </div>
                <div class="view-icon">→</div>
              </div>
              <div class="category-actions">
                <button @click.stop="editCategory(category.id)" class="btn-icon" title="Редагувати">
                  ✏️
                </button>
                <button
                  @click.stop="confirmDelete(category)"
                  class="btn-icon delete"
                  title="Видалити"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            У вас ще немає власних категорій.
            <router-link to="/categories/new">Створити першу</router-link>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import type { Category } from '@/services/categoryService'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const categoryStore = useCategoryStore()

onMounted(async () => {
  await categoryStore.fetchCategories()
})

function editCategory(id: number) {
  router.push(`/categories/${id}/edit`)
}

function viewCategoryExpenses(category: Category) {
  router.push(`/categories/${category.slug}/expenses`)
}

async function confirmDelete(category: Category) {
  if (confirm(`Ви впевнені, що хочете видалити категорію "${category.name}"?`)) {
    const success = await categoryStore.deleteCategory(category.id)
    if (success) {
      alert('Категорію успішно видалено')
    }
  }
}
</script>

<style scoped>
.categories-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.categories-page {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 20px;
}

.btn-retry {
  margin-top: 10px;
  padding: 8px 16px;
  background: #c33;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.categories-content {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.category-section h2 {
  font-size: 24px;
  color: #333;
  margin-bottom: 8px;
}

.section-description {
  color: #666;
  margin-bottom: 20px;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.category-card {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  transition: all 0.3s;
  position: relative;
}

.category-card.clickable {
  cursor: pointer;
}

.category-card.clickable:hover {
  border-color: #667eea;
  background: #f0f4ff;
}

.category-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.category-card.user {
  border-color: #667eea;
}

.card-content {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 15px;
  min-width: 0;
}

.category-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.category-info {
  flex: 1;
  min-width: 0;
}

.category-info h3 {
  margin: 0 0 5px 0;
  font-size: 18px;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.category-badge {
  display: inline-block;
  padding: 3px 8px;
  background: #e0e0e0;
  color: #666;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.category-badge.user {
  background: #e3e7ff;
  color: #667eea;
}

.view-icon {
  color: #667eea;
  font-size: 24px;
  font-weight: bold;
  opacity: 0;
  transition: all 0.3s;
  margin-left: auto;
}

.category-card.clickable:hover .view-icon {
  opacity: 1;
  transform: translateX(5px);
}

.category-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  background: #f0f0f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: #e0e0e0;
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  background: #fee;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 16px;
}

.empty-state a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.empty-state a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .categories-page {
    padding: 20px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
    text-align: center;
  }

  .categories-grid {
    grid-template-columns: 1fr;
  }
}
</style>
