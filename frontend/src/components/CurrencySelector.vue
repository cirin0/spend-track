<script setup lang="ts">
interface Props {
  modelValue?: string
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  modelValue: 'UAH',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const currencies = [
  { code: 'UAH', symbol: '₴', name: 'Гривня' },
  { code: 'EUR', symbol: '€', name: 'Євро' },
  { code: 'USD', symbol: '$', name: 'Долар' },
]

function handleChange(event: Event) {
  const target = event.target as HTMLSelectElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <select :value="modelValue" :disabled="disabled" @change="handleChange" class="currency-selector">
    <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
      {{ currency.symbol }} {{ currency.code }}
    </option>
  </select>
</template>

<style scoped>
.currency-selector {
  padding: 12px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 15px;
  background: var(--input-bg);
  color: var(--text-primary);
  transition: border-color 0.2s;
  cursor: pointer;
}

.currency-selector:focus {
  outline: none;
  border-color: var(--primary-color);
}

.currency-selector:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: var(--secondary-bg);
}
</style>
