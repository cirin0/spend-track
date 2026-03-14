<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useThemeStore } from './stores/theme'
import ToastContainer from './components/ToastContainer.vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()

onMounted(() => {
  authStore.initAuth()
  themeStore.initTheme()
})
</script>

<template>
  <router-view />
  <ToastContainer />
</template>

<style>
:root {
  /* Light theme */
  --bg-primary: #ffffff;
  --bg-secondary: #f8f9fa;
  --card-bg: #ffffff;
  --sidebar-bg: #ffffff;
  --input-bg: #ffffff;
  --text-primary: #1a1a1a;
  --text-secondary: #6b7280;
  --border-color: #e5e7eb;
  --hover-bg: #f3f4f6;
  --secondary-bg: #f3f4f6;
  --primary-color: #2563eb;
  --primary-hover: #1d4ed8;
  --danger-color: #ef4444;
  --success-color: #10b981;
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.1);
}

[data-theme='dark'] {
  --bg-primary: #0f0f0f;
  --bg-secondary: #1a1a1a;
  --card-bg: #1a1a1a;
  --sidebar-bg: #0f0f0f;
  --input-bg: #262626;
  --text-primary: #f5f5f5;
  --text-secondary: #a3a3a3;
  --border-color: #2a2a2a;
  --hover-bg: #262626;
  --secondary-bg: #262626;
  --primary-color: #3b82f6;
  --primary-hover: #2563eb;
  --danger-color: #ef4444;
  --success-color: #10b981;
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.5);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family:
    -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Fira Sans',
    'Droid Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background: var(--bg-primary);
  color: var(--text-primary);
  transition:
    background-color 0.3s,
    color 0.3s;
}

#app {
  min-height: 100vh;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0 !important;
    padding-bottom: 64px;
  }

  .content-wrapper {
    padding: 20px 16px !important;
  }

  .modal {
    width: 95% !important;
    padding: 20px !important;
  }
}
</style>
