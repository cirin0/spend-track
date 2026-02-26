<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useSidebarStore } from '@/stores/sidebar'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const sidebarStore = useSidebarStore()

function toggleSidebar() {
  sidebarStore.toggle()
}

function toggleTheme() {
  themeStore.toggleTheme()
}

function handleLogout() {
  if (confirm('Ви впевнені, що хочете вийти?')) {
    authStore.logout()
  }
}
</script>

<template>
  <aside class="app-sidebar" :class="{ collapsed: sidebarStore.isCollapsed }">
    <div class="sidebar-header">
      <h1 v-if="!sidebarStore.isCollapsed" class="logo">Spend Track</h1>
      <button @click="toggleSidebar" class="toggle-btn" aria-label="Toggle sidebar">
        {{ sidebarStore.isCollapsed ? '→' : '←' }}
      </button>
    </div>

    <nav class="sidebar-nav">
      <router-link to="/" class="nav-item" exact-active-class="active">
        <span class="nav-icon">🏠</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Головна</span>
      </router-link>

      <router-link to="/expenses" class="nav-item" active-class="active">
        <span class="nav-icon">📊</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Витрати</span>
      </router-link>

      <router-link to="/categories" class="nav-item" active-class="active">
        <span class="nav-icon">📁</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Категорії</span>
      </router-link>

      <router-link to="/analytics" class="nav-item" active-class="active">
        <span class="nav-icon">📈</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Аналітика</span>
      </router-link>

      <router-link to="/groups" class="nav-item" active-class="active">
        <span class="nav-icon">👥</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Групи</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <button @click="toggleTheme" class="nav-item theme-toggle">
        <span class="nav-icon">{{ themeStore.theme === 'light' ? '🌙' : '☀️' }}</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">
          {{ themeStore.theme === 'light' ? 'Темна' : 'Світла' }}
        </span>
      </button>

      <router-link to="/profile" class="nav-item" active-class="active">
        <span class="nav-icon">👤</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Профіль</span>
      </router-link>

      <button @click="handleLogout" class="nav-item logout">
        <span class="nav-icon">🚪</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Вийти</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.app-sidebar {
  width: 260px;
  height: 100vh;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 100;
}

.app-sidebar.collapsed {
  width: 90px;
  align-items: center;
}

.sidebar-header {
  padding: 24px 20px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.logo-short {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.toggle-btn {
  width: 45px;
  height: 45px;
  min-width: 36px;
  border-radius: 8px;
  background: var(--hover-bg);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  cursor: pointer;
  color: var(--text-primary);
  transition: all 0.2s;
  flex-shrink: 0;
}

.toggle-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
}

.sidebar-footer {
  padding: 10px 0;
  display: flex;
  flex-direction: column;
  border-top: 1px solid var(--border-color);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 30px;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  font-size: 15px;
}

.nav-item:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
}

.nav-item.active {
  background: var(--primary-color);
  color: white;
  font-weight: 600;
}

.nav-item.logout {
  color: #ef4444;
}

.nav-item.logout:hover {
  background: rgba(239, 68, 68, 0.1);
}

.nav-icon {
  font-size: 20px;
  min-width: 20px;
  text-align: center;
}

.nav-text {
  white-space: nowrap;
}

.theme-toggle {
  margin-bottom: 8px;
}

@media (max-width: 768px) {
  .app-sidebar {
    width: 80px;
  }

  .nav-text {
    display: none;
  }

  .logo {
    display: none;
  }

  .toggle-btn {
    display: none;
  }
}
</style>
