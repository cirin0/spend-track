<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/category'
import type { CreateExpenseData } from '@/services/expenseService'

interface Props {
  title?: string
  initialData?: Partial<CreateExpenseData>
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Додати витрату',
  loading: false,
})

const emit = defineEmits<{
  submit: [data: CreateExpenseData]
  cancel: []
}>()

const categoryStore = useCategoryStore()

const defaultDate = new Date().toISOString().split('T')[0]

const formData = ref<CreateExpenseData>({
  amount: props.initialData?.amount || 0,
  description: props.initialData?.description || '',
  category_id: props.initialData?.category_id,
  date: (props.initialData?.date || defaultDate) as string,
})

const categories = ref(categoryStore.allCategories)

onMounted(async () => {
  if (categoryStore.allCategories.length === 0) {
    await categoryStore.fetchCategories()
  }
  categories.value = categoryStore.allCategories
})

function handleSubmit() {
  emit('submit', formData.value)
}
</script>

<template>
  <div class="expense-form-card">
    <h3>{{ title }}</h3>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="amount">Сума *</label>
        <input
          id="amount"
          v-model.number="formData.amount"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          required
        />
      </div>

      <div class="form-group">
        <label for="description">Опис</label>
        <textarea
          id="description"
          v-model="formData.description"
          placeholder="Опис витрати..."
          rows="3"
        ></textarea>
      </div>

      <div class="form-group">
        <label for="category">Категорія</label>
        <select id="category" v-model="formData.category_id">
          <option :value="undefined">Без категорії</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.icon }} {{ category.name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="date">Дата *</label>
        <input id="date" v-model="formData.date" type="date" required />
      </div>

      <div class="form-actions">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">Скасувати</button>
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Збереження...' : 'Зберегти' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
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

.form-group {
  margin-bottom: 20px;
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

textarea {
  resize: vertical;
  font-family: inherit;
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
