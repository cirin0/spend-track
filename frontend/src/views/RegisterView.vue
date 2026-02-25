<template>
  <div class="register-container">
    <div class="register-card">
      <h1>Реєстрація</h1>

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
            minlength="6"
            placeholder="Мінімум 6 символів"
          />
        </div>

        <div v-if="authStore.error" class="error-message">
          {{ authStore.error }}
        </div>

        <button type="submit" :disabled="authStore.loading">
          {{ authStore.loading ? 'Завантаження...' : 'Зареєструватися' }}
        </button>
      </form>

      <div class="link-section">
        <p>Вже маєте акаунт? <router-link to="/login">Увійти</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
})

async function handleRegister() {
  await authStore.register(form)
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.register-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  margin-bottom: 30px;
  text-align: center;
  color: #333;
  font-size: 28px;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #555;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #667eea;
}

button {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.3s;
}

button:hover:not(:disabled) {
  opacity: 0.9;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  text-align: center;
}

.link-section {
  margin-top: 20px;
  text-align: center;
}

.link-section p {
  color: #666;
}

.link-section a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.link-section a:hover {
  text-decoration: underline;
}
</style>
