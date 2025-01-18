
import { ref } from 'vue';

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