<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import { useSidebarMargin } from '@/composables/useSidebarMargin'
import PageHeader from '@/components/PageHeader.vue'
import AppSidebar from '@/components/AppSidebar.vue'

const router = useRouter()
const groupStore = useGroupStore()
const { marginLeft } = useSidebarMargin()

onMounted(async () => {
  await groupStore.fetchGroups()
})
</script>

<template>
  <div class="page-layout">
    <AppSidebar />
    <main class="main-content" :style="{ marginLeft }">
      <div class="content-wrapper">
        <PageHeader title="Групи" back-to="/">
          <template #actions>
            <router-link to="/groups/new" class="btn-primary">+ Створити групу</router-link>
          </template>
        </PageHeader>

        <div v-if="groupStore.loading && !groupStore.hasGroups" class="loading">
          Завантаження...
        </div>

        <div v-else-if="groupStore.error" class="error-message">
          {{ groupStore.error }}
          <button @click="groupStore.fetchGroups()" class="btn-retry">Спробувати знову</button>
        </div>

        <div v-else>
          <div v-if="groupStore.hasGroups" class="groups-grid">
            <div
              v-for="group in groupStore.groups"
              :key="group.id"
              class="group-card"
              @click="router.push(`/groups/${group.id}`)"
            >
              <div class="group-icon" :style="{ backgroundColor: group.color || '#3b82f6' }">
                {{ group.icon || '👥' }}
              </div>

              <div class="group-info">
                <h3 class="group-name">{{ group.name }}</h3>
                <p v-if="group.description" class="group-description">{{ group.description }}</p>

                <div class="group-meta">
                  <div class="meta-item">
                    <span class="meta-icon">👤</span>
                    <span class="meta-text">{{ group.members_count }} учасників</span>
                  </div>
                  <div v-if="group.owner" class="meta-item">
                    <span class="meta-icon">👑</span>
                    <span class="meta-text">{{ group.owner.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>Немає груп</h3>
            <p>Створіть групу для спільного обліку витрат</p>
            <router-link to="/groups/new" class="btn-primary">Створити першу групу</router-link>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.page-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-primary);
}

.main-content {
  flex: 1;
  transition: margin-left 0.3s ease;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px;
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 700;
  transition: all 0.2s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: var(--primary-color);
  color: white;
  display: inline-block;
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
  font-size: 18px;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-retry {
  margin-top: 10px;
  padding: 10px 20px;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-retry:hover {
  opacity: 0.9;
}

.groups-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.group-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.group-card:hover {
  border-color: var(--primary-color);
  transform: translateY(-5px);
  box-shadow: var(--shadow);
}

.group-icon {
  width: 64px;
  height: 64px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
}

.group-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.group-name {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.group-description {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.group-meta {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: auto;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: var(--text-secondary);
}

.meta-icon {
  font-size: 16px;
}

.meta-text {
  font-weight: 500;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 24px;
  color: var(--text-primary);
  margin-bottom: 10px;
}

.empty-state p {
  color: var(--text-secondary);
  font-size: 16px;
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 80px;
  }

  .content-wrapper {
    padding: 20px;
  }

  .groups-grid {
    grid-template-columns: 1fr;
  }
}
</style>
