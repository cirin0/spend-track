<script setup lang="ts">
interface Props {
  title: string
  subtitle?: string
  icon?: string
  iconColor?: string
  backTo?: string
}

withDefaults(defineProps<Props>(), {
  subtitle: undefined,
  icon: undefined,
  iconColor: undefined,
  backTo: undefined,
})
</script>

<template>
  <div class="page-header">
    <router-link v-if="backTo" :to="backTo" class="btn-back">
      <span class="back-icon">←</span>
    </router-link>

    <div class="header-content">
      <div v-if="icon" class="header-icon" :style="{ backgroundColor: iconColor || '#6b7280' }">
        {{ icon }}
      </div>

      <div class="header-title-section">
        <h1 class="header-title">{{ title }}</h1>
        <p v-if="subtitle" class="header-subtitle">{{ subtitle }}</p>
      </div>
    </div>

    <div v-if="$slots.actions" class="header-actions">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid var(--border-color);
  flex-wrap: wrap;
}

.btn-back {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: var(--hover-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: var(--text-primary);
  font-size: 24px;
  transition: all 0.2s;
  flex-shrink: 0;
  border: 1px solid var(--border-color);
}

.btn-back:hover {
  background: var(--primary-color);
  color: white;
  transform: translateX(-3px);
  border-color: var(--primary-color);
}

.back-icon {
  display: block;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
  min-width: 0;
}

.header-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  flex-shrink: 0;
}

.header-title-section {
  flex: 1;
  min-width: 0;
}

.header-title {
  font-size: 32px;
  color: var(--text-primary);
  margin: 0 0 5px 0;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.header-subtitle {
  display: inline-block;
  padding: 4px 10px;
  background: rgba(37, 99, 235, 0.1);
  color: var(--primary-color);
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

@media (max-width: 768px) {
  .page-header {
    gap: 15px;
  }

  .header-title {
    font-size: 24px;
  }

  .header-icon {
    width: 50px;
    height: 50px;
    font-size: 24px;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }
}
</style>
