<script setup lang="ts">
import { useSidebarStore } from '@/stores/sidebar'

const sidebarStore = useSidebarStore()

function toggleSidebar() {
  sidebarStore.toggle()
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
      <router-link to="/profile" class="nav-item" active-class="active">
        <span class="nav-icon">👤</span>
        <span v-if="!sidebarStore.isCollapsed" class="nav-text">Профіль</span>
      </router-link>
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
  width: 80px;
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
  padding: 12px 28px;
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

@media (max-width: 768px) {
  .app-sidebar {
    width: 100%;
    height: 64px;
    top: auto;
    bottom: 0;
    left: 0;
    right: 0;
    flex-direction: row;
    border-right: none;
    border-top: 1px solid var(--border-color);
    overflow: hidden;
  }

  .sidebar-header {
    display: none;
  }

  .sidebar-nav {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
    padding: 0;
  }

  .sidebar-footer {
    flex-direction: row;
    border-top: none;
    padding: 0;
    align-items: center;
    justify-content: space-around;
  }

  .nav-item {
    flex-direction: column;
    padding: 6px 4px;
    gap: 2px;
    justify-content: center;
    align-items: center;
    width: auto;
    min-width: 44px;
    font-size: 10px;
  }

  .nav-item.active {
    background: transparent;
    color: var(--primary-color);
  }

  .nav-item.active .nav-icon {
    background: rgba(37, 99, 235, 0.1);
    border-radius: 8px;
    padding: 4px 8px;
  }

  .nav-text {
    display: block;
    font-size: 10px;
    line-height: 1;
  }

  .nav-icon {
    font-size: 20px;
  }

  .theme-toggle {
    margin-bottom: 0;
  }
}
</style>
