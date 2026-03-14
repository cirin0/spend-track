<script setup lang="ts">
interface Props {
  amount: number
  currency: string
  convertedAmount?: number
  showConverted?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  convertedAmount: undefined,
  showConverted: true,
})

const currencySymbols: Record<string, string> = {
  UAH: '₴',
  EUR: '€',
  USD: '$',
}

function formatAmount(value: number): string {
  return value.toFixed(2)
}

function getCurrencySymbol(code: string): string {
  return currencySymbols[code] || code
}

const isUAH = props.currency === 'UAH'
const shouldShowConverted = props.showConverted && !isUAH && props.convertedAmount !== undefined
</script>

<template>
  <div class="currency-display">
    <span class="amount-primary">
      {{ formatAmount(amount) }} {{ getCurrencySymbol(currency) }}
    </span>
    <span v-if="shouldShowConverted" class="amount-converted">
      ({{ formatAmount(convertedAmount!) }} ₴)
    </span>
  </div>
</template>

<style scoped>
.currency-display {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.amount-primary {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 15px;
}

.amount-converted {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 400;
}
</style>
