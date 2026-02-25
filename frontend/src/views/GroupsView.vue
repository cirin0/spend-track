<template>
  <div class="groups-container">
    <div class="groups-page">
      <PageHeader title="Групи" back-to="/">
        <template #actions>
          <router-link to="/groups/new" class="btn-primary">+ Створити групу</router-link>
        </template>
      </PageHeader>

      <!-- Loading -->
      <div v-if="groupStore.loading && !groupStore.hasGroups" class="loading">Завантаження...</div>

      <!-- Error -->
      <div v-else-if="groupStore.error" class="error-message">
        {{ groupStore.error }}
        <button @click="groupStore.fetchGroups()" class="btn-retry">Спробувати знову</button>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Groups Grid -->
        <div v-if="groupStore.hasGroups" class="groups-grid">
          <div
            v-for="group in groupStore.groups"
            :key="group.id"
            class="group-card"
            @click="router.push(`/groups/${group.id}`)"
          >
            <div class="group-icon" :style="{ backgroundColor: group.color || '#667eea' }">
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

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">👥</div>
          <h3>Немає груп</h3>
          <p>Створіть групу для спільного обліку витрат</p>
          <router-link to="/groups/new" class="btn-primary">Створити першу групу</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGroupStore } from '@/stores/group'
import PageHeader from '@/components/PageHeader.vue'

const router = useRouter()
const groupStore = useGroupStore()

onMounted(async () => {
  await groupStore.fetchGroups()
})
</script>

<style scoped>
.groups-container {
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.groups-page {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
  cursor: pointer;
  border: none;
  font-size: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: inline-block;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 18px;
}

.error-message {
  background: #fee;
  color: #c33;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.btn-retry {
  margin-top: 10px;
  padding: 8px 16px;
  background: #c33;
  color: white;
  border: none;
  border-radius: 6px;
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
  background: #f8f9fa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.group-card:hover {
  border-color: #667eea;
  transform: translateY(-5px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.group-icon {
  width: 64px;
  height: 64px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  color: #333;
  margin: 0;
}

.group-description {
  font-size: 14px;
  color: #666;
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
  color: #666;
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
  color: #333;
  margin-bottom: 10px;
}

.empty-state p {
  color: #666;
  font-size: 16px;
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .groups-page {
    padding: 20px;
  }

  .groups-grid {
    grid-template-columns: 1fr;
  }
}
</style>
