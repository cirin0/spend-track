<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import { useAuthStore } from '@/stores/auth'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import type { GroupExpense } from '@/services/groupService'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const route = useRoute()
const groupStore = useGroupStore()
const authStore = useAuthStore()
const { marginLeft } = useSidebarMargin()

const groupId = computed(() => Number(route.params.id))
const loading = ref(false)
const activeTab = ref<'expenses' | 'categories' | 'members'>('expenses')

const isOwner = computed(() => {
  return groupStore.currentGroup?.owner.id === authStore.user?.id
})

const isMember = computed(() => {
  return groupStore.currentGroup?.members?.some((m) => m.id === authStore.user?.id)
})

const showAddExpenseModal = ref(false)
const showAddCategoryModal = ref(false)
const showAddMemberModal = ref(false)
const showEditCategoryModal = ref(false)
const showEditExpenseModal = ref(false)

const expenseForm = ref({
  category_id: 0,
  amount: 0,
  date: new Date().toISOString().split('T')[0] as string,
  description: '',
})

const categoryForm = ref({
  name: '',
  icon: '📁',
  color: '#2563eb',
})

const editingCategoryId = ref<number | null>(null)
const editCategoryForm = ref({
  name: '',
  icon: '📁',
  color: '#2563eb',
})

const editingExpenseId = ref<number | null>(null)
const editExpenseForm = ref({
  category_id: 0,
  amount: 0,
  date: new Date().toISOString().split('T')[0] as string,
  description: '',
})

const memberForm = ref({
  user_id: 0,
})

onMounted(async () => {
  loading.value = true
  await groupStore.fetchGroupById(groupId.value)
  await groupStore.fetchCategories(groupId.value)
  await groupStore.fetchExpenses(groupId.value)
  loading.value = false
})

async function handleAddExpense() {
  const result = await groupStore.createExpense(groupId.value, {
    category_id: expenseForm.value.category_id,
    amount: expenseForm.value.amount,
    date: expenseForm.value.date,
    description: expenseForm.value.description,
  })

  if (result) {
    showAddExpenseModal.value = false
    expenseForm.value = {
      category_id: 0,
      amount: 0,
      date: new Date().toISOString().split('T')[0] as string,
      description: '',
    }
  }
}

async function handleAddCategory() {
  const result = await groupStore.createCategory(groupId.value, {
    name: categoryForm.value.name,
    icon: categoryForm.value.icon,
    color: categoryForm.value.color,
  })

  if (result) {
    showAddCategoryModal.value = false
    categoryForm.value = {
      name: '',
      icon: '📁',
      color: '#2563eb',
    }
  }
}

async function handleAddMember() {
  const success = await groupStore.addMember(groupId.value, memberForm.value.user_id)

  if (success) {
    showAddMemberModal.value = false
    memberForm.value = { user_id: 0 }
    await groupStore.fetchGroupById(groupId.value)
  }
}

async function deleteCategoryById(categoryId: number) {
  if (confirm('Ви впевнені, що хочете видалити цю категорію?')) {
    await groupStore.deleteCategory(groupId.value, categoryId)
  }
}

function openEditCategory(category: import('@/services/groupService').GroupCategory) {
  editingCategoryId.value = category.id
  editCategoryForm.value = {
    name: category.name,
    icon: category.icon || '📁',
    color: category.color || '#2563eb',
  }
  showEditCategoryModal.value = true
}

async function handleEditCategory() {
  if (!editingCategoryId.value) return
  const result = await groupStore.updateCategory(groupId.value, editingCategoryId.value, {
    name: editCategoryForm.value.name,
    icon: editCategoryForm.value.icon,
    color: editCategoryForm.value.color,
  })
  if (result) {
    showEditCategoryModal.value = false
    editingCategoryId.value = null
  }
}

async function deleteExpenseById(expenseId: number) {
  if (confirm('Ви впевнені, що хочете видалити цю витрату?')) {
    await groupStore.deleteExpense(groupId.value, expenseId)
  }
}

function openEditExpense(expense: GroupExpense) {
  editingExpenseId.value = expense.id
  editExpenseForm.value = {
    category_id: expense.category_id,
    amount: typeof expense.amount === 'string' ? parseFloat(expense.amount) : expense.amount,
    date: expense.date,
    description: expense.description || '',
  }
  showEditExpenseModal.value = true
}

async function handleEditExpense() {
  if (!editingExpenseId.value) return
  const result = await groupStore.updateExpense(groupId.value, editingExpenseId.value, {
    category_id: editExpenseForm.value.category_id,
    amount: editExpenseForm.value.amount,
    date: editExpenseForm.value.date,
    description: editExpenseForm.value.description,
  })
  if (result) {
    showEditExpenseModal.value = false
    editingExpenseId.value = null
  }
}

async function removeMemberById(userId: number) {
  if (confirm('Ви впевнені, що хочете видалити цього учасника?')) {
    await groupStore.removeMember(groupId.value, userId)
    await groupStore.fetchGroupById(groupId.value)
  }
}

async function handleLeave() {
  if (confirm('Ви впевнені, що хочете вийти з групи?')) {
    const success = await groupStore.leaveGroup(groupId.value)
    if (success) {
      router.push('/groups')
    }
  }
}

function canEditExpense(expense: GroupExpense): boolean {
  return expense.user.id === authStore.user?.id || isOwner.value
}

function formatAmount(amount: number | string): string {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  return num.toFixed(2)
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader
          :title="groupStore.currentGroup?.name || 'Група'"
          :subtitle="groupStore.currentGroup?.description || undefined"
          back-to="/groups"
        >
          <template #actions>
            <button
              v-if="isOwner"
              @click="router.push(`/groups/${groupId}/edit`)"
              class="btn-secondary"
            >
              ✏️ Редагувати
            </button>
            <button v-if="!isOwner" @click="handleLeave" class="btn-danger">
              🚪 Вийти з групи
            </button>
          </template>
        </PageHeader>

        <div v-if="loading" class="loading">Завантаження...</div>

        <div v-else-if="groupStore.error" class="error-message">
          {{ groupStore.error }}
        </div>

        <div v-else class="tabs-container">
          <div class="tabs">
            <button
              class="tab"
              :class="{ active: activeTab === 'expenses' }"
              @click="activeTab = 'expenses'"
            >
              📊 Витрати
            </button>
            <button
              class="tab"
              :class="{ active: activeTab === 'categories' }"
              @click="activeTab = 'categories'"
            >
              📁 Категорії
            </button>
            <button
              class="tab"
              :class="{ active: activeTab === 'members' }"
              @click="activeTab = 'members'"
            >
              👥 Учасники
            </button>
          </div>

          <div v-if="activeTab === 'expenses'" class="tab-content">
            <div class="tab-header">
              <h3>Витрати групи</h3>
              <button @click="showAddExpenseModal = true" class="btn-primary">
                + Додати витрату
              </button>
            </div>

            <div v-if="groupStore.expenses.length > 0" class="expenses-list">
              <div v-for="expense in groupStore.expenses" :key="expense.id" class="expense-item">
                <div class="expense-category">
                  <div
                    class="category-icon-small"
                    :style="{
                      backgroundColor: expense.category?.color || '#6b7280',
                    }"
                  >
                    {{ expense.category?.icon || '📁' }}
                  </div>
                </div>
                <div class="expense-details">
                  <div class="expense-header">
                    <div class="expense-amount">{{ formatAmount(expense.amount) }} ₴</div>
                    <div class="expense-date">{{ formatDate(expense.date) }}</div>
                  </div>
                  <div class="expense-description">{{ expense.description || 'Без опису' }}</div>
                  <div class="expense-meta">{{ expense.user.name }}</div>
                </div>
                <div v-if="canEditExpense(expense)" class="expense-actions">
                  <button @click="openEditExpense(expense)" class="btn-icon edit">✏️</button>
                  <button @click="deleteExpenseById(expense.id)" class="btn-icon delete">🗑️</button>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <p>Немає витрат у цій групі</p>
            </div>
          </div>

          <div v-if="activeTab === 'categories'" class="tab-content">
            <div class="tab-header">
              <h3>Категорії групи</h3>
              <button
                v-if="isOwner || isMember"
                @click="showAddCategoryModal = true"
                class="btn-primary"
              >
                + Додати категорію
              </button>
            </div>

            <div v-if="groupStore.categories.length > 0" class="categories-grid">
              <div
                v-for="category in groupStore.categories"
                :key="category.id"
                class="category-card"
              >
                <div
                  class="category-icon"
                  :style="{ backgroundColor: category.color || '#6b7280' }"
                >
                  {{ category.icon || '📁' }}
                </div>
                <div class="category-name">{{ category.name }}</div>
                <div v-if="isOwner" class="category-actions">
                  <button @click="openEditCategory(category)" class="btn-icon edit">✏️</button>
                  <button @click="deleteCategoryById(category.id)" class="btn-icon delete">
                    🗑️
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <p>Немає категорій у цій групі</p>
            </div>
          </div>

          <div v-if="activeTab === 'members'" class="tab-content">
            <div class="tab-header">
              <h3>Учасники групи</h3>
              <button v-if="isOwner" @click="showAddMemberModal = true" class="btn-primary">
                + Додати учасника
              </button>
            </div>

            <div
              v-if="groupStore.currentGroup?.members && groupStore.currentGroup.members.length > 0"
              class="members-list"
            >
              <div
                v-for="member in groupStore.currentGroup.members"
                :key="member.id"
                class="member-item"
              >
                <div class="member-info">
                  <div class="member-avatar">{{ member.name.charAt(0).toUpperCase() }}</div>
                  <div>
                    <div class="member-name">
                      {{ member.name }}
                      <span
                        v-if="member.id === groupStore.currentGroup?.owner.id"
                        class="owner-badge"
                        >👑 Власник</span
                      >
                    </div>
                    <div class="member-email">{{ member.email }}</div>
                  </div>
                </div>
                <div
                  v-if="isOwner && member.id !== groupStore.currentGroup?.owner.id"
                  class="member-actions"
                >
                  <button @click="removeMemberById(member.id)" class="btn-icon delete">🗑️</button>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <p>Немає учасників</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <div v-if="showAddExpenseModal" class="modal-overlay" @click.self="showAddExpenseModal = false">
      <div class="modal">
        <h3>Додати витрату</h3>
        <form @submit.prevent="handleAddExpense">
          <div class="form-group">
            <label>Категорія</label>
            <select v-model="expenseForm.category_id" required class="form-control">
              <option value="">Виберіть категорію</option>
              <option v-for="cat in groupStore.categories" :key="cat.id" :value="cat.id">
                {{ cat.icon }} {{ cat.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Сума</label>
            <input
              v-model.number="expenseForm.amount"
              type="number"
              step="0.01"
              required
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label>Дата</label>
            <input v-model="expenseForm.date" type="date" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Опис</label>
            <textarea v-model="expenseForm.description" class="form-control"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" @click="showAddExpenseModal = false" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary">Додати</button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showAddCategoryModal"
      class="modal-overlay"
      @click.self="showAddCategoryModal = false"
    >
      <div class="modal">
        <h3>Додати категорію</h3>
        <form @submit.prevent="handleAddCategory">
          <div class="form-group">
            <label>Назва</label>
            <input v-model="categoryForm.name" type="text" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Іконка</label>
            <input v-model="categoryForm.icon" type="text" placeholder="📁" class="form-control" />
          </div>
          <div class="form-group">
            <label>Колір</label>
            <input v-model="categoryForm.color" type="color" class="form-control" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="showAddCategoryModal = false" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary">Додати</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showAddMemberModal" class="modal-overlay" @click.self="showAddMemberModal = false">
      <div class="modal">
        <h3>Додати учасника</h3>
        <form @submit.prevent="handleAddMember">
          <div class="form-group">
            <label>ID користувача</label>
            <input
              v-model.number="memberForm.user_id"
              type="number"
              required
              class="form-control"
              placeholder="Введіть ID користувача"
            />
          </div>
          <div class="modal-actions">
            <button type="button" @click="showAddMemberModal = false" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary">Додати</button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showEditCategoryModal"
      class="modal-overlay"
      @click.self="showEditCategoryModal = false"
    >
      <div class="modal">
        <h3>Редагувати категорію</h3>
        <form @submit.prevent="handleEditCategory">
          <div class="form-group">
            <label>Назва</label>
            <input v-model="editCategoryForm.name" type="text" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Іконка</label>
            <input
              v-model="editCategoryForm.icon"
              type="text"
              placeholder="📁"
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label>Колір</label>
            <input v-model="editCategoryForm.color" type="color" class="form-control" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="showEditCategoryModal = false" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary">Зберегти</button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showEditExpenseModal"
      class="modal-overlay"
      @click.self="showEditExpenseModal = false"
    >
      <div class="modal">
        <h3>Редагувати витрату</h3>
        <form @submit.prevent="handleEditExpense">
          <div class="form-group">
            <label>Категорія</label>
            <select v-model="editExpenseForm.category_id" required class="form-control">
              <option value="">Виберіть категорію</option>
              <option v-for="cat in groupStore.categories" :key="cat.id" :value="cat.id">
                {{ cat.icon }} {{ cat.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Сума</label>
            <input
              v-model.number="editExpenseForm.amount"
              type="number"
              step="0.01"
              required
              class="form-control"
            />
          </div>
          <div class="form-group">
            <label>Дата</label>
            <input v-model="editExpenseForm.date" type="date" required class="form-control" />
          </div>
          <div class="form-group">
            <label>Опис</label>
            <textarea v-model="editExpenseForm.description" class="form-control"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" @click="showEditExpenseModal = false" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary">Зберегти</button>
          </div>
        </form>
      </div>
    </div>
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

.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: var(--secondary-bg);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--hover-bg);
}

.btn-danger {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-danger:hover {
  background: rgba(239, 68, 68, 0.2);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.tabs-container {
  margin-top: 30px;
}

.tabs {
  display: flex;
  gap: 10px;
  border-bottom: 2px solid var(--border-color);
  margin-bottom: 30px;
}

.tab {
  padding: 12px 24px;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 600;
  color: var(--text-secondary);
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
  margin-bottom: -2px;
}

.tab:hover {
  color: var(--primary-color);
}

.tab.active {
  color: var(--primary-color);
  border-bottom-color: var(--primary-color);
}

.tab-content {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.tab-header h3 {
  margin: 0;
  font-size: 20px;
  color: var(--text-primary);
  font-weight: 700;
}

.btn-primary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: var(--primary-color);
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.expenses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.expense-item {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 15px;
  align-items: center;
  transition: all 0.2s;
}

.expense-item:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow);
}

.expense-category {
  flex-shrink: 0;
}

.category-icon-small {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.expense-details {
  flex: 1;
  min-width: 0;
}

.expense-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.expense-amount {
  font-size: 20px;
  font-weight: 700;
  color: var(--primary-color);
}

.expense-date {
  font-size: 14px;
  color: var(--text-secondary);
}

.expense-description {
  color: var(--text-primary);
  font-size: 15px;
  margin-bottom: 4px;
}

.expense-meta {
  font-size: 13px;
  color: var(--text-secondary);
}

.expense-actions {
  flex-shrink: 0;
  display: flex;
  gap: 4px;
}

@media (max-width: 768px) {
  .expense-item {
    flex-direction: column;
    align-items: normal;
  }
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
}

.btn-icon.delete:hover {
  background: rgba(239, 68, 68, 0.1);
}

.btn-icon.edit:hover {
  background: rgba(37, 99, 235, 0.1);
}

.category-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  gap: 4px;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.category-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  position: relative;
  transition: all 0.2s;
}

.category-card:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow);
}

.category-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
}

.category-name {
  font-weight: 600;
  color: var(--text-primary);
  text-align: center;
}

.members-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.member-item {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s;
}

.member-item:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow);
}

.member-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.member-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
}

.member-name {
  font-weight: 600;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}

.owner-badge {
  font-size: 12px;
  background: rgba(234, 179, 8, 0.1);
  color: #ca8a04;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 600;
}

.member-email {
  font-size: 14px;
  color: var(--text-secondary);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: var(--card-bg);
  border-radius: 12px;
  padding: 30px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid var(--border-color);
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: var(--text-primary);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-primary);
  font-size: 14px;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 16px;
  font-family: inherit;
  background: var(--input-bg);
  color: var(--text-primary);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.modal-actions button {
  flex: 1;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .tabs {
    gap: 0px;
  }

  .tab {
    white-space: nowrap;
  }

  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
