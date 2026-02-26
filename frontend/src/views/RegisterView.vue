<script setup lang="ts">
import { reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'

const authStore = useAuthStore()
const themeStore = useThemeStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
})

async function handleRegister() {
  await authStore.register(form)
}

function toggleTheme() {
  themeStore.toggleTheme()
}
</script>

<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>Spend Track</h1>
        <p>Створення акаунту</p>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Ім'я</label>
          <input id="name" v-model="form.name" type="text" required placeholder="Ваше ім'я" />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            placeholder="your@email.com"
          />
        </div>

        <div class="form-group">
          <label for="password">Пароль</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            minlength="8"
          />
        </div>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <button type="submit" class="btn-submit" :disabled="authStore.loading">
          {{ authStore.loading ? 'Завантаження...' : 'Зареєструватися' }}
        </button>
      </form>

      <div class="link-section">
        <p>Вже маєте акаунт? <router-link to="/login">Увійти</router-link></p>
      </div>

      <button @click="toggleTheme" class="theme-toggle-btn">
        {{ themeStore.theme === 'light' ? '🌙' : '☀️' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: var(--bg-primary);
}

.auth-card {
  background: var(--card-bg);
  padding: 48px;
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  width: 100%;
  max-width: 440px;
  border: 1px solid var(--border-color);
  position: relative;
}

.auth-header {
  text-align: center;
  margin-bottom: 40px;
}

.auth-header h1 {
  font-size: 32px;
  color: var(--text-primary);
  margin-bottom: 8px;
  font-weight: 700;
}

.auth-header p {
  color: var(--text-secondary);
  font-size: 16px;
}

.form-group {
  margin-bottom: 24px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-primary);
  font-weight: 600;
  font-size: 14px;
}

input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 15px;
  transition: all 0.2s;
  box-sizing: border-box;
  background: var(--input-bg);
  color: var(--text-primary);
}

input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.btn-submit {
  width: 100%;
  padding: 16px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 14px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
  font-size: 14px;
}

.link-section {
  margin-top: 24px;
  text-align: center;
}

.link-section p {
  color: var(--text-secondary);
  font-size: 14px;
}

.link-section a {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 700;
  transition: color 0.2s;
}

.link-section a:hover {
  color: var(--primary-hover);
}

.theme-toggle-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 40px;
  height: 40px;
  border: none;
  background: var(--hover-bg);
  border-radius: 10px;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.2s;
}

.theme-toggle-btn:hover {
  background: var(--secondary-bg);
  transform: scale(1.1);
}

@media (max-width: 480px) {
  .auth-card {
    padding: 32px 24px;
  }

  .auth-header h1 {
    font-size: 28px;
  }
}
</style>
