<template>
    <header class="header">
      <div class="logo">
        <ProcuradoriaLogoCabecalho :width="140" :height="40" color="#1a237e" />
        </div>
        <span class="portal-text">PORTAL DO ATENDENTE</span>
      
      <div class="user-actions">
        <div class="user-info">
          <div class="username">{{ nomeAtendente }}</div>
          <div class="sector-info"><strong>Setor:</strong> {{ sector }}</div>
          <div class="function-info"><strong>Função:</strong> {{ funcaoAtendente }}</div>
        </div>
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
  import ProcuradoriaLogoCabecalho from '@/components/common/ProcuradoriaLogoCabecalho.vue'
  
  const router = useRouter()
  const nomeAtendente = ref('')
  const funcaoAtendente = ref('')
  const sector = ref('')
  
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
    console.log('Component mounted');
    const attendant = attendantAuthService.getAttendantData()
    console.log('Dados recuperados:', attendant?.sector?.name);
    
    if (attendant) {
        nomeAtendente.value = attendant.name
        funcaoAtendente.value = attendant.function
        sector.value = attendant.sector?.name || ''
        console.log('Nome:', nomeAtendente.value);
        console.log('Função:', funcaoAtendente.value);
        console.log('Sector:', sector.value);
    } else {
        console.log('Nenhum dado de atendente encontrado');
    }
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
  
  .portal-text {
      font-weight: 500;
      color: #1a237e;
  }
  
  .user-actions {
      display: flex;
      align-items: center;
      gap: 15px;
  }
  
  .user-info {
      text-align: right;
  }
  
  .username {
      font-weight: 500;
      color: #333;
      margin-bottom: 2px;
  }
  
  .sector-info, .function-info {
      font-size: 0.8rem;
      line-height: 1.2;
      color: #666;
  }
  
  .sector-info strong, .function-info strong {
      color: #333;
      font-weight: 500;
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