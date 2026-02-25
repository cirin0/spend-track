<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import type { Category } from '@/services/categoryService'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const categoryStore = useCategoryStore()
const { marginLeft } = useSidebarMargin()

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

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
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
          <button @click="categoryStore.fetchCategories()" class="btn-retry">
            Спробувати знову
          </button>
        </div>

        <div v-else class="categories-content">
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
                <div
                  class="category-icon"
                  :style="{ backgroundColor: category.color || '#6b7280' }"
                >
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
                    :style="{ backgroundColor: category.color || '#6b7280' }"
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
                  <button
                    @click.stop="editCategory(category.id)"
                    class="btn-icon"
                    title="Редагувати"
                  >
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
              <br />
              <router-link to="/categories/new" class="btn-create">Створити першу</router-link>
            </div>
          </section>
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px;
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 700;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
  font-size: 18px;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  margin-bottom: 20px;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-retry {
  margin-top: 10px;
  padding: 10px 20px;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.categories-content {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.category-section h2 {
  font-size: 24px;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.section-description {
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.category-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  transition: all 0.2s;
  position: relative;
}

.category-card.clickable {
  cursor: pointer;
}

.category-card.clickable:hover {
  border-color: var(--primary-color);
  transform: translateY(-3px);
  box-shadow: var(--shadow);
}

.category-card.user {
  border-color: var(--primary-color);
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
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.category-badge {
  display: inline-block;
  padding: 3px 8px;
  background: var(--hover-bg);
  color: var(--text-secondary);
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.category-badge.user {
  background: rgba(37, 99, 235, 0.1);
  color: var(--primary-color);
}

.view-icon {
  color: var(--primary-color);
  font-size: 24px;
  font-weight: bold;
  opacity: 0;
  transition: all 0.2s;
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
  background: var(--hover-bg);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: var(--secondary-bg);
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  background: rgba(239, 68, 68, 0.1);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
  font-size: 16px;
}

.empty-state a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 600;
}

.empty-state a:hover {
  text-decoration: underline;
}

.btn-create {
  display: inline-block;
  margin-top: 16px;
  padding: 12px 24px;
  background: var(--primary-color);
  color: white !important;
  text-decoration: none !important;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.2s;
}

.btn-create:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
  text-decoration: none !important;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .categories-grid {
    grid-template-columns: 1fr;
  }
}
</style>
