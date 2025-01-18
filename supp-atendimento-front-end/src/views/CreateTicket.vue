<!-- CreateTicket.vue -->
<template>
    <div class="dashboard">
      <AppHeader />
      <div class="dashboard-layout">
        <AppSidebar />
        <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
          <div class="create-ticket-container">
            <div class="header-section">
              <button class="back-button" @click="$router.back()">
                <span class="icon-text">←</span>
              </button>
              <h2 class="page-title">Novo Atendimento</h2>
            </div>
  
            <div class="form-card">
              <form @submit.prevent="handleSubmit" ref="form">
                <!-- Título do Chamado -->
                <div class="form-group">
                  <label for="title">Título do Chamado*</label>
                  <input
                    id="title"
                    v-model="formData.title"
                    type="text"
                    class="form-input"
                    required
                    placeholder="Digite o título do chamado"
                  >
                </div>
  
                <!-- Setor -->
                <div class="form-group">
                  <label for="sector">Setor*</label>
                  <select
                    id="sector"
                    v-model="formData.sector_id"
                    class="form-input"
                    required
                  >
                    <option value="">Selecione um setor</option>
                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                      {{ sector.name }}
                    </option>
                  </select>
                </div>
  
                <!-- Descrição -->
                <div class="form-group">
                  <label for="description">Descrição*</label>
                  <textarea
                    id="description"
                    v-model="formData.description"
                    class="form-input"
                    rows="5"
                    required
                    placeholder="Descreva detalhadamente o problema"
                  ></textarea>
                </div>
  
                <div class="form-actions">
                  <button 
                    type="button" 
                    class="button button-secondary" 
                    @click="$router.back()"
                  >
                    Cancelar
                  </button>
                  <button 
                    type="submit" 
                    class="button button-primary"
                    :disabled="loading"
                  >
                    {{ loading ? 'Criando...' : 'Criar Atendimento' }}
                  </button>
                </div>
              </form>
            </div>
  
            <!-- Mensagem de feedback -->
            <div v-if="feedback.show" :class="['feedback-message', feedback.type]">
              {{ feedback.message }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useSidebar } from '@/composables/useSidebar';
  import AppHeader from '@/components/common/AppHeader.vue';
  import AppSidebar from '@/components/common/AppSidebar.vue';
  import api from '@/services/api';
  
  const router = useRouter();
  const { sidebarCollapsed } = useSidebar();
  const form = ref(null);
  const loading = ref(false);
  const sectors = ref([]);
  
  const formData = ref({
    title: '',
    description: '',
    sector_id: '',
   // requester_id: 1 // Temporário - deve vir do usuário logado
  });
  
  const feedback = ref({
    show: false,
    message: '',
    type: 'success'
  });
  
  const loadSectors = async () => {
    try {
      const response = await api.get('/sectors');
      sectors.value = response.data.data;
    } catch (error) {
      showFeedback('Erro ao carregar setores', 'error');
    }
  };
  
  const showFeedback = (message, type = 'success') => {
    feedback.value = {
      show: true,
      message,
      type
    };
    setTimeout(() => {
      feedback.value.show = false;
    }, 3000);
  };
  
  const handleSubmit = async () => {
    if (!formData.value.title || !formData.value.description || !formData.value.sector_id) {
      showFeedback('Por favor, preencha todos os campos obrigatórios', 'error');
      return;
    }
  
    loading.value = true;
    
    try {
      const response = await api.post('/service', formData.value);
      
      if (response.data.success) {
        showFeedback('Atendimento criado com sucesso!');
        setTimeout(() => {
          router.push('/tickets');
        }, 1500);
      }
    } catch (error) {
      showFeedback(error.response?.data?.message || 'Erro ao criar atendimento', 'error');
    } finally {
      loading.value = false;
    }
  };
  
  onMounted(() => {
    loadSectors();
  });
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
  
  .create-ticket-container {
    padding: 24px;
    background-color: #f8f9fa;
    min-height: calc(100vh - 60px);
  }
  
  .header-section {
    display: flex;
    align-items: center;
    margin-bottom: 24px;
  }
  
  .back-button {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 8px;
    margin-right: 16px;
    color: #1a237e;
  }
  
  .page-title {
    font-size: 1.5rem;
    font-weight: 500;
    color: #1a237e;
  }
  
  .form-card {
    background: white;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
  }
  
  .form-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
  }
  
  .form-input:focus {
    outline: none;
    border-color: #1a237e;
  }
  
  textarea.form-input {
    resize: vertical;
    min-height: 120px;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
  }
  
  .button {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: background-color 0.2s;
  }
  
  .button-primary {
    background-color: #1a237e;
    color: white;
  }
  
  .button-primary:hover {
    background-color: #0d47a1;
  }
  
  .button-primary:disabled {
    background-color: #9fa8da;
    cursor: not-allowed;
  }
  
  .button-secondary {
    background-color: #e0e0e0;
    color: #333;
  }
  
  .button-secondary:hover {
    background-color: #bdbdbd;
  }
  
  .feedback-message {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 12px 24px;
    border-radius: 4px;
    color: white;
    z-index: 1000;
  }
  
  .feedback-message.success {
    background-color: #4caf50;
  }
  
  .feedback-message.error {
    background-color: #f44336;
  }
  </style>