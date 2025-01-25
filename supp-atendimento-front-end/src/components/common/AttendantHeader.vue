<template>
    <header class="header">
      <div class="logo">
        <img src="/assets/supp.png" alt="SUPP/PBH" class="logo-img">
        <span class="portal-text">Portal do Atendente</span>
      </div>
      <div class="user-actions">
        <span class="username">{{ nomeAtendente }}</span>
        <span class="function">{{ funcaoAtendente }}</span>
        <button @click="fazerLogout" class="logout-button">
          Sair
        </button>
      </div>
    </header>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { attendantAuthService } from '@/services/attendant-auth.service'
  
  const router = useRouter()
  const nomeAtendente = ref('')
  const funcaoAtendente = ref('')
  
  const fazerLogout = async () => {
      try {
          await attendantAuthService.logout()
          window.location.href = '/attendant/login'
      } catch (error) {
          console.error('Erro no logout:', error)
          window.location.href = '/attendant/login'
      }
  }
  
  onMounted(() => {
      const attendant = attendantAuthService.getAttendantData()
      nomeAtendente.value = attendant?.name || 'Atendente'
      funcaoAtendente.value = attendant?.function || ''
  })
  </script>
  
  <style scoped>
  .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: white;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      z-index: 1000;
  }
  
  .logo {
      display: flex;
      align-items: center;
      gap: 12px;
  }
  
  .logo-img {
      height: 40px;
  }
  
  .portal-text {
      font-weight: 500;
      color: #1a237e;
  }
  
  .user-actions {
      display: flex;
      align-items: center;
      gap: 15px;
  }
  
  .username {
      font-weight: 500;
      color: #333;
  }
  
  .function {
      color: #666;
      font-size: 0.875rem;
  }
  
  .logout-button {
      padding: 8px 16px;
      background-color: #1a237e;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
  }
  
  .logout-button:hover {
      background-color: #0d47a1;
  }
  </style>