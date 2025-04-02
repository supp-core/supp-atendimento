// Em composables/useSidebar.js
import { ref } from 'vue';

// Estado reativo compartilhado
const sidebarCollapsed = ref(false);

export function useSidebar() {
  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  };

  return {
    sidebarCollapsed,
    toggleSidebar
  };
}