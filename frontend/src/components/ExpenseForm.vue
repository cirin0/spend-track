<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useCategoryStore } from '@/stores/category'
import { useCurrency } from '@/composables/useCurrency'
import CurrencySelector from '@/components/CurrencySelector.vue'
import type { CreateExpenseData } from '@/services/expenseService'

interface Props {
  title?: string
  initialData?: Partial<CreateExpenseData>
  loading?: boolean
  validationErrors?: Record<string, string[]>
  categories?: Array<{ id: number; name: string; icon?: string | null }>
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Додати витрату',
  loading: false,
  validationErrors: () => ({}),
  categories: undefined,
})

const emit = defineEmits<{
  submit: [data: CreateExpenseData]
  cancel: []
  retry: []
}>()

const categoryStore = useCategoryStore()
const { fetchRates, getRate, convertToUAH, formatCurrency, ratesUnavailable } = useCurrency()

const defaultDate = new Date().toISOString().split('T')[0]

const formData = ref<CreateExpenseData>({
  amount: props.initialData?.amount as number,
  currency: props.initialData?.currency || 'UAH',
  converted_amount: props.initialData?.converted_amount || 0,
  exchange_rate: props.initialData?.exchange_rate || 1.0,
  description: props.initialData?.description || '',
  category_id: props.initialData?.category_id,
  date: (props.initialData?.date || defaultDate) as string,
})

const categories = ref(props.categories || categoryStore.allCategories)
const networkError = ref(false)

// Computed properties for conversion preview
const currentRate = computed(() => {
  return getRate(formData.value.currency as 'UAH' | 'EUR' | 'USD')
})

const convertedAmount = computed(() => {
  return convertToUAH(formData.value.amount, formData.value.currency as 'UAH' | 'EUR' | 'USD')
})

const showConversionPreview = computed(() => {
  return formData.value.currency !== 'UAH' && formData.value.amount > 0
})

const currencySelectorDisabled = computed(() => {
  return ratesUnavailable.value
})

// Get validation error for a field
function getFieldError(field: string): string | null {
  return props.validationErrors?.[field]?.[0] || null
}

// Check if field has error
function hasFieldError(field: string): boolean {
  return !!props.validationErrors?.[field]
}

// Watch for changes in amount or currency to update converted_amount and exchange_rate
watch([() => formData.value.amount, () => formData.value.currency], () => {
  formData.value.exchange_rate = currentRate.value
  formData.value.converted_amount = convertedAmount.value
})

// Watch for rates unavailable and reset to UAH
watch(ratesUnavailable, (unavailable) => {
  if (unavailable && formData.value.currency !== 'UAH') {
    formData.value.currency = 'UAH'
    formData.value.exchange_rate = 1.0
    formData.value.converted_amount = formData.value.amount
  }
})

onMounted(async () => {
  // Fetch currency rates
  try {
    await fetchRates()
  } catch (e) {
    console.error('Failed to fetch currency rates:', e)
    networkError.value = true
  }

  // Fetch categories only if not provided via props
  if (!props.categories) {
    if (categoryStore.allCategories.length === 0) {
      await categoryStore.fetchCategories()
    }
    categories.value = categoryStore.allCategories
  }
})

function handleSubmit() {
  emit('submit', formData.value)
}

function handleRetry() {
  networkError.value = false
  emit('retry')
}
</script>

<template>
  <h3>{{ title }}</h3>

  <div v-if="networkError" class="error-banner">
    <div class="error-content">
      <span class="error-icon">⚠️</span>
      <div class="error-text">
        <strong>Помилка мережі</strong>
        <p>Не вдалося підключитися до сервера. Перевірте з'єднання.</p>
      </div>
    </div>
    <button @click="handleRetry" class="btn-retry">Спробувати знову</button>
  </div>

  <div v-if="ratesUnavailable" class="warning-banner">
    <span class="warning-icon">ℹ️</span>
    <span>Курси валют недоступні. Використовується тільки UAH.</span>
  </div>

  <form @submit.prevent="handleSubmit">
    <div class="form-row">
      <div class="form-group" :class="{ 'has-error': hasFieldError('amount') }">
        <label for="amount">Сума *</label>
        <input
          id="amount"
          v-model.number="formData.amount"
          type="number"
          min="0"
          placeholder="0.00"
          required
          :class="{ 'input-error': hasFieldError('amount') }"
        />
        <span v-if="hasFieldError('amount')" class="error-message">
          {{ getFieldError('amount') }}
        </span>
      </div>

      <div class="form-group" :class="{ 'has-error': hasFieldError('currency') }">
        <label for="currency">Валюта *</label>
        <CurrencySelector v-model="formData.currency" :disabled="currencySelectorDisabled" />
        <span v-if="hasFieldError('currency')" class="error-message">
          {{ getFieldError('currency') }}
        </span>
      </div>
    </div>

    <div v-if="showConversionPreview" class="conversion-preview">
      <div class="preview-label">Конвертована сума:</div>
      <div class="preview-amount">{{ formatCurrency(convertedAmount, 'UAH') }}</div>
      <div class="preview-rate">Курс: {{ currentRate.toFixed(4) }}</div>
    </div>

    <div class="form-group" :class="{ 'has-error': hasFieldError('description') }">
      <label for="description">Опис</label>
      <textarea
        id="description"
        v-model="formData.description"
        placeholder="Опис витрати..."
        rows="3"
        :class="{ 'input-error': hasFieldError('description') }"
      ></textarea>
      <span v-if="hasFieldError('description')" class="error-message">
        {{ getFieldError('description') }}
      </span>
    </div>

    <div class="form-group" :class="{ 'has-error': hasFieldError('category_id') }">
      <label for="category">Категорія</label>
      <select
        id="category"
        v-model="formData.category_id"
        :class="{ 'input-error': hasFieldError('category_id') }"
      >
        <option :value="undefined">Без категорії</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.icon }} {{ category.name }}
        </option>
      </select>
      <span v-if="hasFieldError('category_id')" class="error-message">
        {{ getFieldError('category_id') }}
      </span>
    </div>

    <div class="form-group" :class="{ 'has-error': hasFieldError('date') }">
      <label for="date">Дата *</label>
      <input
        id="date"
        v-model="formData.date"
        type="date"
        required
        :class="{ 'input-error': hasFieldError('date') }"
      />
      <span v-if="hasFieldError('date')" class="error-message">
        {{ getFieldError('date') }}
      </span>
    </div>

    <div class="form-actions">
      <button type="button" @click="$emit('cancel')" class="btn-secondary">Скасувати</button>
      <button type="submit" class="btn-primary" :disabled="loading || networkError">
        {{ loading ? 'Збереження...' : 'Зберегти' }}
      </button>
    </div>
  </form>
</template>

<style scoped>
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
  appearance: textfield;
}

.expense-form-card {
  background: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

h3 {
  margin-bottom: 20px;
  color: var(--text-primary);
  font-size: 20px;
}

.error-banner,
.warning-banner {
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.error-banner {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.warning-banner {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--text-primary);
}

.error-content {
  flex: 1;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.error-icon,
.warning-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.error-text {
  flex: 1;
}

.error-text strong {
  display: block;
  color: var(--danger-color);
  margin-bottom: 4px;
}

.error-text p {
  color: var(--text-secondary);
  font-size: 14px;
  margin: 0;
}

.btn-retry {
  padding: 8px 16px;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  white-space: nowrap;
}

.btn-retry:hover {
  opacity: 0.9;
}

.form-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-row .form-group {
  margin-bottom: 0;
}

.form-group.has-error label {
  color: var(--danger-color);
}

label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-primary);
  font-weight: 500;
  font-size: 14px;
}

input,
textarea,
select {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 15px;
  background: var(--input-bg);
  color: var(--text-primary);
  transition: border-color 0.2s;
}

input:focus,
textarea:focus,
select:focus {
  outline: none;
  border-color: var(--primary-color);
}

input.input-error,
textarea.input-error,
select.input-error {
  border-color: var(--danger-color);
}

input.input-error:focus,
textarea.input-error:focus,
select.input-error:focus {
  border-color: var(--danger-color);
}

.error-message {
  display: block;
  color: var(--danger-color);
  font-size: 13px;
  margin-top: 6px;
}

textarea {
  resize: vertical;
  font-family: inherit;
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

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: var(--hover-bg);
}
</style>
