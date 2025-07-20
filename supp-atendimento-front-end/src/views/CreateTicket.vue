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

            <form @submit.prevent="handleSubmit" ref="form" enctype="multipart/form-data">
              <!-- Número do Ticket -->
              <div class="form-group">
                <div class="auto-title">{{ nextTicketTitle }}</div>
              </div>

              <!-- Campo de categoria -->
              <div class="form-group">
                <label for="category">Categoria*</label>
                <select id="category" v-model="formData.category_id" class="form-input" required>
                  <option value="">Selecione uma categoria</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>


              <!-- Descrição -->
              <div class="form-group">
                <label for="description">Descrição*</label>
                <textarea id="description" v-model="formData.description" class="form-input" rows="5" required
                  placeholder="Descreva detalhadamente o problema. No caso de planilhas, informe por favor qual planilha ou cole o link da mesma."></textarea>
              </div>

              <div class="anexos">
                <label for="fileInput">Anexos</label>
                <input type="file" id="fileInput" ref="fileInput" @change="handleFileSelect" multiple
                  accept=".pdf,.docx,.jpg,.png,.gif">
                <div v-if="selectedFiles.length > 0" class="selected-files">
                  <div v-for="(file, index) in selectedFiles" :key="index" class="file-item">
                    {{ file.name }}
                    <button type="button" @click="removeFile(index)" class="remove-file">×</button>
                  </div>
                </div>
              </div>

              <div class="actions">
                <v-btn @click="cancelar">Cancelar</v-btn>
                <v-btn type="submit" color="primary" :loading="loading" :disabled="loading">
                  Criar Atendimento
                </v-btn>
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
import { authService } from '@/services/auth.service';
import AppHeader from '@/components/common/AppHeader.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import api from '@/services/api';


const router = useRouter();
const { sidebarCollapsed } = useSidebar();
const form = ref(null);
const loading = ref(false);
const sectors = ref([]);
const categories = ref([]);
const serviceTypes = ref([]);
const selectedFiles = ref([]);
const nextTicketTitle = ref('Carregando...');

const formData = ref({
  description: '',
  category_id: '',
  priority: 'NORMAL', // Valor padrão
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

const loadCategories = async () => {
  try {
    const response = await api.get('/categories');
    categories.value = response.data.data;
  } catch (error) {
    showFeedback('Erro ao carregar categorias', 'error');
  }
};

const loadServiceTypes = async () => {
  try {
    const response = await api.get('/service-types');
    serviceTypes.value = response.data.data;
  } catch (error) {
    console.error('Erro ao carregar tipos de serviço:', error);
    showFeedback('Erro ao carregar tipos de serviço', 'error');
  }
};

const handleFileSelect = (event) => {
  const files = event.target.files;
  for (let i = 0; i < files.length; i++) {
    selectedFiles.value.push(files[i]);
  }
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

const loadNextTicketNumber = async () => {
  try {
    // Busca os tickets do usuário para obter IDs reais do banco
    const response = await api.get('/service/my-tickets?per_page=1000');
    
    // Se retornou dados, calcula próximo ID baseado no maior ID existente
    if (response.data && response.data.data && Array.isArray(response.data.data) && response.data.data.length > 0) {
      const allIds = response.data.data.map(ticket => parseInt(ticket.id)).filter(id => !isNaN(id));
      if (allIds.length > 0) {
        const maxId = Math.max(...allIds);
        const nextNumber = maxId + 1;
        nextTicketTitle.value = `Ticket ${nextNumber}`;
        console.log(`Próximo ticket baseado no banco: ${nextNumber} (maior ID atual: ${maxId})`);
        return;
      }
    }
    
    // Se chegou aqui, não há tickets ainda (banco vazio)
    nextTicketTitle.value = `Ticket 1`;
    console.log('Banco vazio, iniciando com Ticket 1');
    
  } catch (error) {
    // 404 significa que não há tickets ainda - é o estado inicial normal
    if (error.response?.status === 404) {
      nextTicketTitle.value = `Ticket 1`;
      console.log('404 = banco vazio, iniciando com Ticket 1');
    } else {
      console.error('Erro ao buscar tickets:', error);
      // Para outros erros, tentar uma segunda abordagem
      try {
        // Tenta uma busca mais simples sem filtros
        const fallbackResponse = await api.get('/service/my-tickets');
        if (fallbackResponse.data?.data?.length > 0) {
          const allIds = fallbackResponse.data.data.map(ticket => parseInt(ticket.id)).filter(id => !isNaN(id));
          if (allIds.length > 0) {
            const maxId = Math.max(...allIds);
            nextTicketTitle.value = `Ticket ${maxId + 1}`;
            return;
          }
        }
        // Se não retornou dados, assume primeiro ticket
        nextTicketTitle.value = `Ticket 1`;
      } catch (fallbackError) {
        console.error('Erro no fallback, assumindo Ticket 1:', fallbackError);
        nextTicketTitle.value = `Ticket 1`;
      }
    }
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
  if (!formData.value.description || !formData.value.category_id) {
    showFeedback('Por favor, preencha todos os campos obrigatórios', 'error');
    return;
  }

  loading.value = true;

  try {
    // Pega o usuário logado
    const userData = authService.getUser();
    if (!userData || !userData.id) {
      showFeedback('Erro: usuário não autenticado', 'error');
      return;
    }

    // Busca o setor "Suporte" pelo nome
    let suporteSectorId = 15; // Fallback
    if (sectors.value && sectors.value.length > 0) {
      const suporteSector = sectors.value.find(sector => 
        sector.name && sector.name.toLowerCase().includes('suporte')
      );
      if (suporteSector) {
        suporteSectorId = suporteSector.id;
        console.log(`Setor "Suporte" encontrado com ID: ${suporteSectorId}`);
      } else {
        // Se não encontrar "Suporte", usa o primeiro disponível
        suporteSectorId = sectors.value[0].id;
        console.log(`Setor "Suporte" não encontrado, usando o primeiro: ${suporteSectorId}`);
      }
    }

    // Busca o service type "Triagem" pelo nome
    let triagemServiceTypeId = 1; // Fallback
    if (serviceTypes.value && serviceTypes.value.length > 0) {
      const triagemType = serviceTypes.value.find(st => 
        st.name && st.name.toLowerCase().includes('triagem')
      );
      if (triagemType) {
        triagemServiceTypeId = triagemType.id;
        console.log(`Service type "Triagem" encontrado com ID: ${triagemServiceTypeId}`);
      } else {
        // Se não encontrar "Triagem", usa o primeiro disponível
        triagemServiceTypeId = serviceTypes.value[0].id;
        console.log(`Service type "Triagem" não encontrado, usando o primeiro: ${triagemServiceTypeId}`);
      }
    }

    // Cria FormData em vez de JSON object (igual ao AdminCreateTicket)
    const submitData = new FormData();
    
    // Adiciona campos básicos
    submitData.append('title', nextTicketTitle.value);
    submitData.append('description', formData.value.description);
    submitData.append('priority', formData.value.priority);
    submitData.append('category_id', formData.value.category_id);
    submitData.append('sector_id', suporteSectorId);
    submitData.append('requester_id', userData.id);
    submitData.append('service_type_id', triagemServiceTypeId);
    submitData.append('created_by_admin', 'false');

    // Log para debug
    console.log('FormData sendo enviado:');
    for (let pair of submitData.entries()) {
      console.log(pair[0] + ': ' + pair[1]);
    }

    const response = await api.post('/service', submitData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

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
  loadCategories();
  loadServiceTypes();
  loadNextTicketNumber();
});




const handleFileUpload = (event) => {
  files.value = event.target.files;
};


const cancelar = () => {
  router.push('/tickets');
};

</script>

<style scoped>
.anexos {
  margin: 1rem 0;
}


.anexos small {
  color: #666;
  font-size: 0.875rem;
}


.actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.anexos input[type="file"] {
  display: block;
  margin: 0.5rem 0;
}

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
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

.auto-title {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  padding: 12px;
  font-size: 1rem;
  font-weight: 500;
  color: #1a237e;
  margin-bottom: 4px;
}

.title-note {
  color: #666;
  font-style: italic;
  font-size: 0.875rem;
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


<script>
export default {
  data() {
    return {
      selectedFiles: [],
      // ... outros dados
    }
  },
  methods: {

    handleFileDrop(event) {
      this.addFiles(event.dataTransfer.files)
    },
    addFiles(fileList) {
      const maxSize = 10 * 1024 * 1024 // 10MB

      Array.from(fileList).forEach(file => {
        if (file.size <= maxSize) {
          this.selectedFiles.push(file)
        } else {
          this.$notify({
            type: 'error',
            message: `Arquivo ${file.name} excede o tamanho máximo permitido`
          })
        }
      })
    },

    async handleSubmit() {
      const formData = new FormData()

      // Adicionar dados do ticket
      formData.append('title', this.title)
      formData.append('description', this.description)
      formData.append('priority', 'NORMAL')

      // Adicionar arquivos
      this.selectedFiles.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file)
      })

      try {
        await this.createTicket(formData)
        // ... continua
      } catch (error) {
        // ... tratamento de erro
      }
    }
  }
}
</script>