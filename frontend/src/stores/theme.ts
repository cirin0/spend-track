import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export type Theme = 'light' | 'dark'

export const useThemeStore = defineStore('theme', () => {
  const theme = ref<Theme>('light')

  // Ініціалізація теми з localStorage
  function initTheme() {
    const savedTheme = localStorage.getItem('theme') as Theme | null
    if (savedTheme) {
      theme.value = savedTheme
    } else {
      // Перевіряємо системні налаштування
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
      theme.value = prefersDark ? 'dark' : 'light'
    }
    applyTheme()
  }

  // Застосувати тему до документа
  function applyTheme() {
    document.documentElement.setAttribute('data-theme', theme.value)
  }

  // Перемикання теми
  function toggleTheme() {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    localStorage.setItem('theme', theme.value)
    applyTheme()
  }

  // Встановити конкретну тему
  function setTheme(newTheme: Theme) {
    theme.value = newTheme
    localStorage.setItem('theme', theme.value)
    applyTheme()
  }

  // Спостерігаємо за змінами теми
  watch(theme, () => {
    applyTheme()
  })

  return {
    theme,
    initTheme,
    toggleTheme,
    setTheme,
  }
})
