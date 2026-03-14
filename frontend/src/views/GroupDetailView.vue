<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import { useAuthStore } from '@/stores/auth'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import { useCurrency } from '@/composables/useCurrency'
import { useToast } from '@/composables/useToast'
import { userService, type User } from '@/services/userService'
import type { GroupExpense } from '@/services/groupService'
import type { CreateExpenseData } from '@/services/expenseService'
import type { AxiosError } from 'axios'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import CurrencyDisplay from '@/components/CurrencyDisplay.vue'
import ExpenseForm from '@/components/ExpenseForm.vue'

const router = useRouter()
const route = useRoute()
const groupStore = useGroupStore()
const authStore = useAuthStore()
const { marginLeft } = useSidebarMargin()
const { fetchRates, getRate } = useCurrency()
const toast = useToast()

const selectedCurrency = ref<'UAH' | 'USD' | 'EUR'>('UAH')

function cycleCurrency() {
  const currencies: Array<'UAH' | 'USD' | 'EUR'> = ['UAH', 'USD', 'EUR']
  const currentIndex = currencies.indexOf(selectedCurrency.value)
  const nextIndex = (currentIndex + 1) % currencies.length
  selectedCurrency.value = currencies[nextIndex]!
}

function convertAmount(amountInUAH: number, toCurrency: 'UAH' | 'USD' | 'EUR'): number {
  if (toCurrency === 'UAH') return amountInUAH

  const rate = getRate(toCurrency)
  if (rate === 0 || rate === 1) return amountInUAH

  return amountInUAH / rate
}

const displayAmount = computed(() => {
  const converted = convertAmount(totalConverted.value, selectedCurrency.value)
  return formatAmount(converted)
})

const currencySymbol = computed(() => {
  const symbols = {
    UAH: '₴',
    USD: '$',
    EUR: '€',
  }
  return symbols[selectedCurrency.value]
})

const currentRate = computed(() => {
  if (selectedCurrency.value === 'UAH') return null
  return getRate(selectedCurrency.value)
})

const groupId = computed(() => {
  const param = route.params.slug || route.params.id
  if (!param) return ''
  return typeof param === 'string' ? param : String(param[0])
})
const isSlug = computed(() => isNaN(Number(groupId.value)))
const loading = ref(false)
const activeTab = ref<'expenses' | 'categories' | 'members'>('expenses')

const isOwner = computed(() => {
  return groupStore.currentGroup?.owner.id === authStore.user?.id
})

const isMember = computed(() => {
  return groupStore.currentGroup?.members?.some((m) => m.id === authStore.user?.id)
})

const totalConverted = computed(() => {
  return groupStore.expenses.reduce((sum, exp) => sum + (exp.converted_amount || exp.amount), 0)
})

const showAddExpenseModal = ref(false)
const showAddCategoryModal = ref(false)
const showAddMemberModal = ref(false)
const showEditCategoryModal = ref(false)
const showEditExpenseModal = ref(false)

const validationErrors = ref<Record<string, string[]>>({})
const editExpenseData = ref<Partial<CreateExpenseData> | undefined>(undefined)

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

const searchQuery = ref('')
const searchResults = ref<User[]>([])
const searchLoading = ref(false)
const selectedUser = ref<User | null>(null)
const addMemberError = ref('')

let searchTimeout: ReturnType<typeof setTimeout> | null = null

async function searchUsers() {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }

  searchLoading.value = true
  try {
    searchResults.value = await userService.search(searchQuery.value)
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  } finally {
    searchLoading.value = false
  }
}

function handleSearchInput() {
  // Очищаємо попередній таймер
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  // Якщо менше 2 символів - очищаємо результати одразу
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    searchLoading.value = false
    return
  }

  // Показуємо індикатор завантаження
  searchLoading.value = true

  // Встановлюємо новий таймер на 500ms
  searchTimeout = setTimeout(() => {
    searchUsers()
  }, 500)
}

function selectUser(user: User) {
  // Перевіряємо чи користувач вже в групі
  if (isUserInGroup(user.id)) {
    addMemberError.value = `${user.name} вже є учасником цієї групи`
    return
  }

  selectedUser.value = user
  // Не заповнюємо інпут, просто очищаємо результати
  searchQuery.value = ''
  searchResults.value = []
  addMemberError.value = ''
  searchLoading.value = false
}

function clearSearch() {
  searchQuery.value = ''
  searchResults.value = []
  selectedUser.value = null
  addMemberError.value = ''
  searchLoading.value = false
  // Очищаємо таймер
  if (searchTimeout) {
    clearTimeout(searchTimeout)
    searchTimeout = null
  }
}

function clearSelectedUser() {
  selectedUser.value = null
  addMemberError.value = ''
}

function isUserInGroup(userId: number): boolean {
  return groupStore.currentGroup?.members?.some((m) => m.id === userId) || false
}

onMounted(async () => {
  loading.value = true
  if (isSlug.value) {
    await groupStore.fetchGroupBySlug(groupId.value)
  } else {
    await groupStore.fetchGroupById(Number(groupId.value))
  }
  const numericId = groupStore.currentGroup?.id
  if (numericId) {
    await groupStore.fetchCategories(numericId)
    await groupStore.fetchExpenses(numericId)
  }
  loading.value = false

  // Fetch currency rates
  await fetchRates()
})

async function handleAddExpense(data: CreateExpenseData) {
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  validationErrors.value = {}

  try {
    const result = await groupStore.createExpense(numericId, data)

    if (result) {
      showAddExpenseModal.value = false
      toast.success('Витрату успішно додано!')
    }
  } catch (error) {
    const axiosError = error as AxiosError<{ errors?: Record<string, string[]>; message?: string }>

    if (axiosError.response?.status === 422 && axiosError.response.data.errors) {
      validationErrors.value = axiosError.response.data.errors
      toast.error('Будь ласка, виправте помилки у формі')
    } else if (axiosError.response?.data?.message) {
      toast.error(axiosError.response.data.message)
    } else {
      toast.error('Не вдалося створити витрату')
    }
  }
}

async function handleAddCategory() {
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  const result = await groupStore.createCategory(numericId, {
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
  const numericId = groupStore.currentGroup?.id
  if (!numericId || !selectedUser.value) return

  addMemberError.value = ''
  const success = await groupStore.addMember(numericId, selectedUser.value.id)

  if (success) {
    showAddMemberModal.value = false
    clearSearch()
    await groupStore.fetchGroupById(numericId)
  } else {
    // Показуємо помилку на формі
    if (groupStore.error) {
      // Перевіряємо різні типи помилок
      const errorLower = groupStore.error.toLowerCase()
      if (errorLower.includes('already') || errorLower.includes('вже є')) {
        addMemberError.value = `${selectedUser.value.name} вже є учасником цієї групи`
      } else if (errorLower.includes('not found') || errorLower.includes('не знайдено')) {
        addMemberError.value = 'Користувача не знайдено'
      } else if (errorLower.includes('only owner') || errorLower.includes('тільки власник')) {
        addMemberError.value = 'Тільки власник може додавати учасників'
      } else {
        addMemberError.value = groupStore.error
      }
    } else {
      addMemberError.value = 'Не вдалося додати учасника. Спробуйте ще раз'
    }
  }
}

function openAddMemberModal() {
  clearSearch()
  addMemberError.value = ''
  showAddMemberModal.value = true
}

function closeAddMemberModal() {
  showAddMemberModal.value = false
  clearSearch()
  addMemberError.value = ''
  // Очищаємо таймер при закритті
  if (searchTimeout) {
    clearTimeout(searchTimeout)
    searchTimeout = null
  }
}

async function deleteCategoryById(categoryId: number) {
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  if (confirm('Ви впевнені, що хочете видалити цю категорію?')) {
    await groupStore.deleteCategory(numericId, categoryId)
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
  const numericId = groupStore.currentGroup?.id
  if (!editingCategoryId.value || !numericId) return
  const result = await groupStore.updateCategory(numericId, editingCategoryId.value, {
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
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  if (confirm('Ви впевнені, що хочете видалити цю витрату?')) {
    await groupStore.deleteExpense(numericId, expenseId)
  }
}

function openEditExpense(expense: GroupExpense) {
  editingExpenseId.value = expense.id
  editExpenseData.value = {
    category_id: expense.category_id,
    amount: typeof expense.amount === 'string' ? parseFloat(expense.amount) : expense.amount,
    currency: expense.currency || 'UAH',
    converted_amount: expense.converted_amount || expense.amount,
    exchange_rate: expense.exchange_rate || 1.0,
    date: expense.date,
    description: expense.description || '',
  }
  showEditExpenseModal.value = true
}

async function handleEditExpense(data: CreateExpenseData) {
  const numericId = groupStore.currentGroup?.id
  if (!editingExpenseId.value || !numericId) return

  validationErrors.value = {}

  try {
    const result = await groupStore.updateExpense(numericId, editingExpenseId.value, data)

    if (result) {
      showEditExpenseModal.value = false
      editingExpenseId.value = null
      editExpenseData.value = undefined
      toast.success('Витрату успішно оновлено!')
    }
  } catch (error) {
    const axiosError = error as AxiosError<{ errors?: Record<string, string[]>; message?: string }>

    if (axiosError.response?.status === 422 && axiosError.response.data.errors) {
      validationErrors.value = axiosError.response.data.errors
      toast.error('Будь ласка, виправте помилки у формі')
    } else if (axiosError.response?.data?.message) {
      toast.error(axiosError.response.data.message)
    } else {
      toast.error('Не вдалося оновити витрату')
    }
  }
}

function handleCancelAddExpense() {
  showAddExpenseModal.value = false
  validationErrors.value = {}
}

function handleCancelEditExpense() {
  showEditExpenseModal.value = false
  editingExpenseId.value = null
  editExpenseData.value = undefined
  validationErrors.value = {}
}

async function handleRetry() {
  try {
    await fetchRates()
    toast.info('Спроба завантажити курси валют...')
  } catch {
    toast.error('Не вдалося завантажити курси валют')
  }
}

async function removeMemberById(userId: number) {
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  if (confirm('Ви впевнені, що хочете видалити цього учасника?')) {
    await groupStore.removeMember(numericId, userId)
    await groupStore.fetchGroupById(numericId)
  }
}

async function handleLeave() {
  const numericId = groupStore.currentGroup?.id
  if (!numericId) return

  if (confirm('Ви впевнені, що хочете вийти з групи?')) {
    const success = await groupStore.leaveGroup(numericId)
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
              @click="router.push(`/groups/${groupStore.currentGroup?.slug}/edit`)"
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

            <div v-if="groupStore.expenses.length > 0" class="stats-section">
              <div class="stat-card">
                <div class="stat-label">Всього витрат</div>
                <div class="stat-value">{{ groupStore.expenses.length }}</div>
              </div>
              <div class="stat-card clickable" @click="cycleCurrency">
                <div class="stat-label">
                  Загальна сума
                  <span class="currency-hint">Натисніть для зміни валюти</span>
                </div>
                <div class="stat-value">{{ displayAmount }} {{ currencySymbol }}</div>
                <div class="stat-note">
                  <span v-if="selectedCurrency !== 'UAH'">
                    Конвертовано з UAH за курсом НБУ
                    <br />
                    <small
                      >на сьогодні 1 {{ selectedCurrency }} = {{ currentRate?.toFixed(2) }} ₴</small
                    >
                  </span>
                </div>
              </div>
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
                  <div class="category-name">{{ expense.category?.name || 'Без категорії' }}</div>
                </div>
                <div class="expense-details">
                  <div class="expense-header">
                    <CurrencyDisplay
                      :amount="expense.amount"
                      :currency="expense.currency"
                      :converted-amount="expense.converted_amount"
                      :show-converted="true"
                    />
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
              <button v-if="isOwner" @click="openAddMemberModal" class="btn-primary">
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
                  <div class="member-avatar">
                    <img
                      v-if="member.avatar"
                      :src="member.avatar"
                      :alt="member.name"
                      class="member-avatar-image"
                    />
                    <div v-else class="member-avatar-placeholder">
                      {{ member.name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
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

    <div v-if="showAddExpenseModal" class="modal-overlay" @click.self="handleCancelAddExpense">
      <div class="modal">
        <ExpenseForm
          title="Додати витрату"
          :categories="groupStore.categories"
          :loading="groupStore.loading"
          :validation-errors="validationErrors"
          @submit="handleAddExpense"
          @cancel="handleCancelAddExpense"
          @retry="handleRetry"
        />
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
            <div class="color-input-group">
              <input v-model="categoryForm.color" type="color" class="color-picker-input" />
              <input
                v-model="categoryForm.color"
                type="text"
                class="form-control color-text-input"
                placeholder="#2563eb"
                pattern="^#[0-9A-Fa-f]{6}$"
                maxlength="7"
              />
            </div>
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

    <div v-if="showAddMemberModal" class="modal-overlay" @click.self="closeAddMemberModal">
      <div class="modal">
        <h3>Додати учасника</h3>
        <form @submit.prevent="handleAddMember">
          <div class="form-group">
            <label>Пошук користувача</label>

            <div v-if="!selectedUser">
              <div class="search-container">
                <input
                  v-model="searchQuery"
                  @input="handleSearchInput"
                  type="text"
                  class="form-control"
                  placeholder="Введіть ім'я або email (мінімум 2 символи)"
                  autocomplete="off"
                />
                <button
                  v-if="searchQuery"
                  type="button"
                  @click="clearSearch"
                  class="clear-search-btn"
                >
                  ✕
                </button>
              </div>

              <div v-if="searchLoading" class="search-loading">Пошук...</div>

              <div v-if="searchResults.length > 0" class="search-results">
                <div
                  v-for="user in searchResults"
                  :key="user.id"
                  class="search-result-item"
                  :class="{ 'already-member': isUserInGroup(user.id) }"
                  @click="selectUser(user)"
                >
                  <div class="user-avatar-small">{{ user.name.charAt(0).toUpperCase() }}</div>
                  <div class="user-info-small">
                    <div class="user-name-small">
                      {{ user.name }}
                      <span v-if="isUserInGroup(user.id)" class="member-badge">Вже в групі</span>
                    </div>
                    <div class="user-email-small">{{ user.email }}</div>
                  </div>
                </div>
              </div>

              <div
                v-if="!searchLoading && searchQuery.length >= 2 && searchResults.length === 0"
                class="no-results"
              >
                Користувачів не знайдено
              </div>
            </div>

            <div v-if="selectedUser" class="selected-user">
              <div class="selected-user-label">Вибраний користувач:</div>
              <div class="selected-user-badge">
                <div class="user-avatar-small">
                  {{ selectedUser.name.charAt(0).toUpperCase() }}
                </div>
                <div class="user-info-small">
                  <div class="user-name-small">{{ selectedUser.name }}</div>
                  <div class="user-email-small">{{ selectedUser.email }}</div>
                </div>
                <button
                  type="button"
                  @click="clearSelectedUser"
                  class="remove-selected-btn"
                  title="Видалити вибір"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeAddMemberModal" class="btn-secondary">
              Скасувати
            </button>
            <button type="submit" class="btn-primary" :disabled="!selectedUser">Додати</button>
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
            <div class="color-input-group">
              <input v-model="editCategoryForm.color" type="color" class="color-picker-input" />
              <input
                v-model="editCategoryForm.color"
                type="text"
                class="form-control color-text-input"
                placeholder="#2563eb"
                pattern="^#[0-9A-Fa-f]{6}$"
                maxlength="7"
              />
            </div>
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

    <div v-if="showEditExpenseModal" class="modal-overlay" @click.self="handleCancelEditExpense">
      <div class="modal">
        <ExpenseForm
          title="Редагувати витрату"
          :initial-data="editExpenseData"
          :categories="groupStore.categories"
          :loading="groupStore.loading"
          :validation-errors="validationErrors"
          @submit="handleEditExpense"
          @cancel="handleCancelEditExpense"
          @retry="handleRetry"
        />
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

.btn-secondary:hover:not(:disabled) {
  background: var(--hover-bg);
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
  padding: 24px;
  border-radius: 12px;
  color: white;
  transition: all 0.3s ease;
}

.stat-card.clickable {
  cursor: pointer;
  position: relative;
}

.stat-card.clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
}

.stat-card.clickable:active {
  transform: translateY(-2px);
}

[data-theme='light'] .stat-card {
  background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
  margin-bottom: 8px;
  font-weight: 600;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.currency-hint {
  font-size: 10px;
  opacity: 0.6;
  font-weight: 400;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
}

.stat-note {
  font-size: 11px;
  opacity: 0.7;
  margin-top: 4px;
  font-weight: 400;
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

.btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: var(--primary-color);
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
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
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

.category-name {
  font-size: 11px;
  color: var(--text-secondary);
  text-align: center;
  max-width: 80px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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

.expense-header :deep(.currency-display) {
  flex-shrink: 0;
}

.expense-header :deep(.amount-primary) {
  font-size: 20px;
  font-weight: 700;
  color: var(--primary-color);
}

.expense-header :deep(.amount-converted) {
  font-size: 13px;
  color: var(--text-secondary);
  font-weight: 400;
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
  overflow: hidden;
  border: 2px solid var(--primary-color);
}

.member-avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.member-avatar-placeholder {
  width: 100%;
  height: 100%;
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

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}

.form-row .form-group {
  margin-bottom: 0;
}

.conversion-preview {
  background: var(--secondary-bg);
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid var(--border-color);
}

.preview-label {
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 4px;
}

.preview-amount {
  font-size: 20px;
  font-weight: 600;
  color: var(--primary-color);
  margin-bottom: 4px;
}

.preview-rate {
  font-size: 12px;
  color: var(--text-secondary);
}

.color-picker-input:hover {
  border-color: var(--primary-color);
}

.color-text-input {
  flex: 1;
}

.search-container {
  position: relative;
}

.clear-search-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 18px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
}

.clear-search-btn:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
}

.search-loading {
  padding: 12px;
  text-align: center;
  color: var(--text-secondary);
  font-size: 14px;
}

.search-results {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  margin-top: 8px;
}

.search-result-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid var(--border-color);
}

.search-result-item:last-child {
  border-bottom: none;
}

.search-result-item:hover {
  background: var(--hover-bg);
}

.search-result-item.already-member {
  opacity: 0.6;
  cursor: default;
}

.search-result-item.already-member:hover {
  background: transparent;
}

.member-badge {
  display: inline-block;
  font-size: 11px;
  padding: 2px 8px;
  background: rgba(37, 99, 235, 0.1);
  color: var(--primary-color);
  border-radius: 12px;
  font-weight: 600;
  margin-left: 8px;
}

.user-avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  flex-shrink: 0;
}

.user-info-small {
  flex: 1;
  min-width: 0;
}

.user-name-small {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.user-email-small {
  font-size: 13px;
  color: var(--text-secondary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.no-results {
  padding: 20px;
  text-align: center;
  color: var(--text-secondary);
  font-size: 14px;
}

.selected-user {
  margin-top: 12px;
}

.selected-user-label {
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 8px;
  font-weight: 600;
}

.selected-user-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(37, 99, 235, 0.1);
  border: 1px solid rgba(37, 99, 235, 0.3);
  border-radius: 8px;
  position: relative;
}

.remove-selected-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(0, 0, 0, 0.1);
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
  line-height: 1;
}

.remove-selected-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: var(--danger-color);
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
