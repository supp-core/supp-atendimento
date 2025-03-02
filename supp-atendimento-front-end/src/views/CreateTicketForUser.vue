<template>
    <div class="dashboard">
      <AttendantHeader />
      <div class="dashboard-layout">
        <AttendantSidebar />
        <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
          <div class="create-ticket-container">
            <div class="header-section">
              <button class="back-button" @click="$router.back()">
                <span class="icon-text">←</span>
              </button>
              <h2 class="page-title">Criar Atendimento para Usuário</h2>
            </div>
  
            <div class="form-card">
              <v-form @submit.prevent="submitForm" ref="form">
                <!-- Seleção de Usuário -->
                <div class="form-group">
                  <v-autocomplete
                    v-model="formData.userId"
                    :items="users"
                    item-title="name"
                    item-value="id"
                    label="Selecione o Usuário*"
                    placeholder="Digite para buscar usuários"
                    :loading="loadingUsers"
                    return-object
                    required
                    clearable
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item v-bind="props">
                        <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                        <v-list-item-subtitle>{{ item.raw.email }}</v-list-item-subtitle>
                      </v-list-item>
                    </template>
                  </v-autocomplete>
                </div>
  
                <!-- Título do Chamado -->
                <div class="form-group">
                  <v-text-field
                    v-model="formData.title"
                    label="Título do Chamado*"
                    required
                    placeholder="Digite o título do chamado"
                  ></v-text-field>
                </div>
  
                <!-- Prioridade -->
                <div class="form-group">
                  <v-select
                    v-model="formData.priority"
                    :items="priorityOptions"
                    item-title="title"
                    item-value="value"
                    label="Prioridade*"
                    required
                  ></v-select>
                </div>
  
                <!-- Setor -->
                <div class="form-group">
                  <v-select
                    v-model="formData.sectorId"
                    :items="sectors"
                    item-title="name"
                    item-value="id"
                    label="Setor*"
                    required
                    :loading="loadingSectors"
                  ></v-select>
                </div>
  
                <!-- Descrição -->
                <div class="form-group">
                  <v-textarea
                    v-model="formData.description"
                    label="Descrição*"
                    required
                    rows="5"
                    placeholder="Descreva detalhadamente o problema"
                  ></v-textarea>
                </div>
  
                <!-- Anexos -->
                <div class="form-group">
                  <label>Anexos</label>
                  <v-file-input
                    v-model="selectedFiles"
                    accept=".pdf,.docx,.jpg,.png,.gif"
                    placeholder="Selecione os arquivos"
                    prepend-icon="mdi-paperclip"
                    label="Anexos"
                    multiple
                    show-size
                    counter
                  ></v-file-input>
                </div>
  
                <!-- Botões de Ação -->
                <div class="actions">
                  <v-btn color="grey" text @click="cancelar">Cancelar</v-btn>
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="loading"
                    :disabled="loading || !isFormValid"
                  >
                    Criar Atendimento
                  </v-btn>
                </div>
              </v-form>
            </div>
  
            <!-- Mensagem de feedback -->
            <v-snackbar v-model="feedback.show" :color="feedback.type">
              {{ feedback.message }}
              <template v-slot:actions>
                <v-btn
                  variant="text"
                  @click="feedback.show = false"
                >
                  Fechar
                </v-btn>
              </template>
            </v-snackbar>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useSidebar } from '@/composables/useSidebar';
  import AttendantHeader from '@/components/common/AttendantHeader.vue';
  import AttendantSidebar from '@/components/common/AttendantSidebar.vue';
  import api from '@/services/api';
  import { attendantAuthService } from '@/services/attendant-auth.service';
  
  const router = useRouter();
  const { sidebarCollapsed } = useSidebar();
  const form = ref(null);
  const loading = ref(false);
  const loadingUsers = ref(false);
  const loadingSectors = ref(false);
  const users = ref([]);
  const sectors = ref([]);
  const selectedFiles = ref([]);
  
  const formData = ref({
    userId: null,
    title: '',
    description: '',
    sectorId: '',
    priority: 'NORMAL',
  });
  
  const priorityOptions = [
    { title: 'Baixa', value: 'BAIXA' },
    { title: 'Normal', value: 'NORMAL' },
    { title: 'Alta', value: 'ALTA' },
    { title: 'Urgente', value: 'URGENTE' }
  ];
  
  const feedback = ref({
    show: false,
    message: '',
    type: 'success'
  });
  
  const isFormValid = computed(() => {
    return formData.value.userId && 
           formData.value.title && 
           formData.value.description && 
           formData.value.sectorId;
  });
  
  const loadUsers = async () => {
    loadingUsers.value = true;
    try {
      const response = await api.get('/users');
      if (response.data.success) {
        users.value = response.data.data;
      }
    } catch (error) {
      showFeedback('Erro ao carregar usuários', 'error');
      console.error('Erro ao carregar usuários:', error);
    } finally {
      loadingUsers.value = false;
    }
  };
  
  const loadSectors = async () => {
    loadingSectors.value = true;
    try {
      const response = await api.get('/sectors');
      if (response.data.success) {
        sectors.value = response.data.data;
      }
    } catch (error) {
      showFeedback('Erro ao carregar setores', 'error');
      console.error('Erro ao carregar setores:', error);
    } finally {
      loadingSectors.value = false;
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
    }, 5000);
  };
  
  const submitForm = async () => {
    if (!isFormValid.value) {
      showFeedback('Por favor, preencha todos os campos obrigatórios', 'error');
      return;
    }
  
    loading.value = true;
  
    try {
      // Criação do FormData para envio
      const submitData = new FormData();
      
      // Adicionando campos básicos
      submitData.append('title', formData.value.title);
      submitData.append('description', formData.value.description);
      submitData.append('priority', formData.value.priority);
      submitData.append('sector_id', formData.value.sectorId);
      submitData.append('user_id', formData.value.userId.id);
      submitData.append('created_by_admin', 'true');
      
      // Adicionando arquivos
      if (selectedFiles.value.length > 0) {
        selectedFiles.value.forEach((file) => {
          submitData.append('files[]', file);
        });
      }
  
      // Envio para a API
      const response = await api.post('/service/admin/create', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
  
      if (response.data.success) {
        showFeedback('Atendimento criado com sucesso!', 'success');
        setTimeout(() => {
          router.push('/attendant/tickets');
        }, 1500);
      }
    } catch (error) {
      showFeedback(error.response?.data?.message || 'Erro ao criar atendimento', 'error');
      console.error('Erro ao criar ticket:', error);
    } finally {
      loading.value = false;
    }
  };
  
  const cancelar = () => {
    router.push('/attendant/tickets');
  };
  
  onMounted(() => {
    // Verificar se o usuário é admin
    const attendant = attendantAuthService.getAttendantData();
    if (!attendant || attendant.function !== 'Admin') {
      showFeedback('Acesso não autorizado. Apenas administradores podem criar chamados para usuários.', 'error');
      router.push('/attendant/dashboard');
      return;
    }
  
    loadUsers();
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
    min-height: calc(100vh - 108px);
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 32px;
  }
  </style>