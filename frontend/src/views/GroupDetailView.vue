<template>
  <div class="group-detail-container">
    <div class="group-detail-page">
      <PageHeader
        :title="groupStore.currentGroup?.name || 'Група'"
        :subtitle="groupStore.currentGroup?.description || undefined"
        :icon="groupStore.currentGroup?.icon || undefined"
        :icon-color="groupStore.currentGroup?.color || undefined"
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
          <button v-if="!isOwner" @click="handleLeave" class="btn-danger">🚪 Вийти з групи</button>
        </template>
      </PageHeader>

      <!-- Loading -->
      <div v-if="loading" class="loading">Завантаження...</div>

      <!-- Error -->
      <div v-else-if="groupStore.error" class="error-message">
        {{ groupStore.error }}
      </div>

      <!-- Tabs -->
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

        <!-- Expenses Tab -->
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
                    backgroundColor: expense.category?.color || '#e0e0e0',
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
                <button @click="deleteExpenseById(expense.id)" class="btn-icon delete">🗑️</button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <p>Немає витрат у цій групі</p>
          </div>
        </div>

        <!-- Categories Tab -->
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
            <div v-for="category in groupStore.categories" :key="category.id" class="category-card">
              <div class="category-icon" :style="{ backgroundColor: category.color || '#e0e0e0' }">
                {{ category.icon || '📁' }}
              </div>
              <div class="category-name">{{ category.name }}</div>
              <div v-if="isOwner" class="category-actions">
                <button @click="deleteCategoryById(category.id)" class="btn-icon delete">🗑️</button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <p>Немає категорій у цій групі</p>
          </div>
        </div>

        <!-- Members Tab -->
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
                    <span v-if="member.id === groupStore.currentGroup?.owner.id" class="owner-badge"
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

    <!-- Add Expense Modal (Simple) -->
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

    <!-- Add Category Modal -->
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

    <!-- Add Member Modal -->
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import { useAuthStore } from '@/stores/auth'
import type { GroupExpense } from '@/services/groupService'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const route = useRoute()
const groupStore = useGroupStore()
const authStore = useAuthStore()

const groupId = computed(() => Number(route.params.id))
const loading = ref(false)
const activeTab = ref<'expenses' | 'categories' | 'members'>('expenses')

const isOwner = computed(() => {
  return groupStore.currentGroup?.owner.id === authStore.user?.id
})

const isMember = computed(() => {
  return groupStore.currentGroup?.members?.some((m) => m.id === authStore.user?.id)
})

// Modals
const showAddExpenseModal = ref(false)
const showAddCategoryModal = ref(false)
const showAddMemberModal = ref(false)

const expenseForm = ref({
  category_id: 0,
  amount: 0,
  date: new Date().toISOString().split('T')[0] as string,
  description: '',
})

const categoryForm = ref({
  name: '',
  icon: '📁',
  color: '#667eea',
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
    description: expenseForm.value.description || undefined,
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
    icon: categoryForm.value.icon || undefined,
    color: categoryForm.value.color || undefined,
  })

  if (result) {
    showAddCategoryModal.value = false
    categoryForm.value = {
      name: '',
      icon: '📁',
      color: '#667eea',
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

async function deleteExpenseById(expenseId: number) {
  if (confirm('Ви впевнені, що хочете видалити цю витрату?')) {
    await groupStore.deleteExpense(groupId.value, expenseId)
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

<style scoped>
.group-detail-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.group-detail-page {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: #f0f0f0;
  color: #333;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #e0e0e0;
}

.btn-danger {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: #fee;
  color: #c33;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-danger:hover {
  background: #fdd;
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.tabs-container {
  margin-top: 30px;
}

.tabs {
  display: flex;
  gap: 10px;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 30px;
}

.tab {
  padding: 12px 24px;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 600;
  color: #666;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
  margin-bottom: -2px;
}

.tab:hover {
  color: #667eea;
}

.tab.active {
  color: #667eea;
  border-bottom-color: #667eea;
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
  color: #333;
}

.btn-primary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.expenses-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.expense-item {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 15px;
  align-items: center;
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
  color: #667eea;
}

.expense-date {
  font-size: 14px;
  color: #666;
}

.expense-description {
  color: #333;
  font-size: 15px;
  margin-bottom: 4px;
}

.expense-meta {
  font-size: 13px;
  color: #999;
}

.expense-actions {
  flex-shrink: 0;
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
}

.btn-icon.delete:hover {
  background: #fee;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.category-card {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  position: relative;
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
  color: #333;
  text-align: center;
}

.category-actions {
  position: absolute;
  top: 10px;
  right: 10px;
}

.members-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.member-item {
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
}

.member-name {
  font-weight: 600;
  color: #333;
  display: flex;
  align-items: center;
  gap: 8px;
}

.owner-badge {
  font-size: 12px;
  background: #fff3cd;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 600;
}

.member-email {
  font-size: 14px;
  color: #666;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
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
  background: white;
  border-radius: 12px;
  padding: 30px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
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
  .group-detail-page {
    padding: 20px;
  }

  .tabs {
    overflow-x: auto;
  }

  .tab {
    white-space: nowrap;
  }

  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
