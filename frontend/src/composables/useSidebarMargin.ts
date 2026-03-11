import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useSidebarStore } from '@/stores/sidebar'

export function useSidebarMargin() {
  const sidebarStore = useSidebarStore()
  const isMobile = ref(false)

  const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768
  }

  onMounted(() => {
    checkMobile()
    window.addEventListener('resize', checkMobile)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', checkMobile)
  })

  const marginLeft = computed(() => {
    if (isMobile.value) {
      return '0px'
    }
    return sidebarStore.isCollapsed ? '80px' : '260px'
  })

  return {
    marginLeft,
  }
}
