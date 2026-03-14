<script setup lang="ts">
import { computed } from 'vue'
import type { Expense } from '@/services/expenseService'
import CurrencyDisplay from './CurrencyDisplay.vue'

interface Props {
  expense: Expense | null
  show: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  close: []
  edit: [id: number]
  delete: [expense: Expense]
}>()

const isNonUAH = computed(() => {
  return props.expense?.currency && props.expense.currency !== 'UAH'
})

const exchangeRateDate = computed(() => {
  if (!props.expense?.date) return ''
  const date = new Date(props.expense.date)
  return date.toLocaleDateString('uk-UA', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('uk-UA', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatExchangeRate(rate: number): string {
  return rate.toFixed(4)
}

function handleEdit() {
  if (props.expense) {
    emit('edit', props.expense.id)
  }
}

function handleDelete() {
  if (props.expense) {
    emit('delete', props.expense)
  }
}

function handleClose() {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show && expense" class="modal-overlay" @click="handleClose">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Деталі витрати</h2>
            <button class="btn-close" @click="handleClose" title="Закрити">✕</button>
          </div>

          <div class="modal-body">
            <div class="detail-section">
              <div class="detail-row">
                <span class="detail-label">Категорія</span>
                <div class="category-info">
                  <div
                    class="category-icon"
                    :style="{ backgroundColor: expense.category?.color || '#6b7280' }"
                  >
                    {{ expense.category?.icon || '📁' }}
                  </div>
                  <span class="category-name">{{ expense.category?.name || 'Без категорії' }}</span>
                </div>
              </div>

              <div class="detail-row">
                <span class="detail-label">Дата</span>
                <span class="detail-value">{{ formatDate(expense.date) }}</span>
              </div>

              <div class="detail-row">
                <span class="detail-label">Опис</span>
                <span class="detail-value">{{ expense.description || 'Без опису' }}</span>
              </div>
            </div>

            <div class="currency-section">
              <h3>Інформація про валюту</h3>

              <div class="currency-details">
                <div class="currency-row primary">
                  <span class="currency-label">Оригінальна сума</span>
                  <CurrencyDisplay
                    :amount="expense.amount"
                    :currency="expense.currency"
                    :converted-amount="expense.converted_amount"
                    :show-converted="false"
                  />
                </div>

                <div v-if="isNonUAH" class="currency-row">
                  <span class="currency-label">Конвертована сума</span>
                  <div class="converted-value">
                    {{ expense.converted_amount?.toFixed(2) || expense.amount.toFixed(2) }} ₴
                  </div>
                </div>

                <div v-if="isNonUAH" class="currency-row">
                  <span class="currency-label">Курс обміну</span>
                  <div class="exchange-rate-value">
                    1 {{ expense.currency }} =
                    {{ formatExchangeRate(expense.exchange_rate || 1) }} ₴
                  </div>
                </div>

                <div v-if="isNonUAH" class="currency-row">
                  <span class="currency-label">Дата курсу</span>
                  <div class="exchange-date-value">{{ exchangeRateDate }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="handleEdit" class="btn-secondary">✏️ Редагувати</button>
            <button @click="handleDelete" class="btn-danger">🗑️ Видалити</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: var(--card-bg);
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.btn-close {
  width: 36px;
  height: 36px;
  border: none;
  background: var(--hover-bg);
  border-radius: 8px;
  cursor: pointer;
  font-size: 20px;
  color: var(--text-secondary);
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

.modal-body {
  padding: 24px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 16px;
  color: var(--text-primary);
  font-weight: 500;
  text-align: right;
  max-width: 60%;
  word-wrap: break-word;
}

.category-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.category-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.category-name {
  font-size: 16px;
  color: var(--text-primary);
  font-weight: 500;
}

.currency-section {
  background: var(--hover-bg);
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
}

.currency-section h3 {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 16px 0;
}

.currency-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.currency-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
}

.currency-row.primary {
  padding-bottom: 16px;
  border-bottom: 2px solid var(--border-color);
}

.currency-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
}

.currency-row.primary .currency-label {
  font-size: 16px;
  color: var(--text-primary);
}

.currency-row :deep(.currency-display) {
  font-size: 16px;
}

.currency-row.primary :deep(.amount-primary) {
  font-size: 28px;
  font-weight: 700;
  color: var(--primary-color);
}

.converted-value {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-primary);
}

.exchange-rate-value {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
  font-family: 'Courier New', monospace;
}

.exchange-date-value {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 500;
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 24px;
  border-top: 1px solid var(--border-color);
}

.btn-secondary,
.btn-danger {
  flex: 1;
  padding: 14px 24px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-secondary {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: var(--hover-bg);
  transform: translateY(-2px);
}

.btn-danger {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
}

.btn-danger:hover {
  background: var(--danger-color);
  color: white;
  transform: translateY(-2px);
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.9);
}

@media (max-width: 768px) {
  .modal-content {
    max-width: 100%;
    border-radius: 16px 16px 0 0;
    max-height: 95vh;
  }

  .modal-header h2 {
    font-size: 20px;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .detail-value {
    max-width: 100%;
    text-align: left;
  }

  .currency-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .modal-footer {
    flex-direction: column;
  }
}
</style>
