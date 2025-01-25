<!-- src/views/AttendantDashboard.vue -->
<template>
    <div class="dashboard">
      <AppHeader />
      <div class="dashboard-layout">
        <AppSidebar />
        <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
          <h1>Dashboard do Atendente</h1>
          <!-- Conteúdo do dashboard -->
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { useSidebar } from '@/composables/useSidebar';
  import AppHeader from '@/components/common/AppHeader.vue';
  import AppSidebar from '@/components/common/AppSidebar.vue';
  
  const { sidebarCollapsed } = useSidebar();
  </script>
  
  <style scoped>
  .dashboard {
    min-height: 100vh;
    background-color: #f3f4f6;
  }
  
  .dashboard-layout {
    padding-top: 60px;
    min-height: calc(100vh - 60px);
  }
  
  .dashboard-content {
    transition: margin-left 0.3s ease;
    padding: 24px;
  }
  </style>