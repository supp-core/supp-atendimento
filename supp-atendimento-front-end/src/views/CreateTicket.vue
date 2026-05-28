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

            <form @submit.prevent="submitForm" ref="form" enctype="multipart/form-data">
              <!-- Título do Chamado -->
              <div class="form-group">
                <label for="title">Título do Chamado*</label>
                <input id="title" v-model="formData.title" type="text" class="form-input" required
                  placeholder="Digite o título do chamado">
              </div>

              <!-- Descrição -->
              <div class="form-group">
                <label for="description">Descrição*</label>
                <textarea id="description" v-model="formData.description" class="form-input" rows="5" required
                  placeholder="Descreva detalhadamente o problema"></textarea>
              </div>

              <!-- Projeto (opcional) -->
              <div class="form-group">
                <label for="project">Projeto (opcional)</label>
                <select id="project" v-model="formData.project_id" class="form-input">
                  <option value="">Sem projeto</option>
                  <option v-for="p in projects" :key="p.id" :value="p.id">
                    [{{ p.acronym }}] {{ p.name }}
                  </option>
                </select>
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
import AppHeader from '@/components/common/AppHeader.vue';
import AppSidebar from '@/components/common/AppSidebar.vue';
import api from '@/services/api';


const router = useRouter();
const { sidebarCollapsed } = useSidebar();
const loading = ref(false);
const sectors = ref([]);
const selectedFiles = ref([]);


const projects = ref([])

const formData = ref({
  title: '',
  description: '',
  sector_id: '',
  priority: 'NORMAL',
  project_id: '',
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

const handleFileSelect = (event) => {
  const files = event.target.files;
  for (let i = 0; i < files.length; i++) {
    selectedFiles.value.push(files[i]);
  }
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
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

const loadProjects = async () => {
  try {
    const response = await api.get('/project')
    projects.value = (response.data.data || []).filter(p => p.status === 'ATIVO')
  } catch (e) {
    // silently ignore
  }
}

onMounted(() => {
  loadSectors();
  loadProjects();
});




const submitForm = async () => {
  try {
    loading.value = true;

    const submitData = new FormData();
    submitData.append('title', formData.value.title);
    submitData.append('description', formData.value.description);
    submitData.append('priority', 'NORMAL');
    const adminSector = sectors.value.find(s => s.name === 'Admin');
    submitData.append('sector_id', adminSector?.id ?? '');
    if (formData.value.project_id) {
      submitData.append('project_id', formData.value.project_id);
    }
    selectedFiles.value.forEach(file => {
      submitData.append('files[]', file);
    });

    await api.post('/service', submitData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    showFeedback('Atendimento criado com sucesso!');
    formData.value.title = '';
    formData.value.description = '';
    formData.value.project_id = '';
    selectedFiles.value = [];
    setTimeout(() => router.push('/tickets'), 2000);
  } catch (error) {
    showFeedback(error.response?.data?.message || 'Erro ao criar o atendimento. Por favor, tente novamente.', 'error');
  } finally {
    loading.value = false;
  }
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

