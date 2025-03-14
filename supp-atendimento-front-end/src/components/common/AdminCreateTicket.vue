<template>
  <v-dialog v-model="showDialog" max-width="800px">
    <v-card>
      <v-card-title class="headline primary text-white">
        Criar Novo Atendimento para Usuário
      </v-card-title>

      <v-card-text class="pt-4">
        <v-form ref="form" @submit.prevent="submitForm">
          <!-- Campos do formulário -->
          <v-row>
            <!-- Seleção de Usuário -->
            <v-col cols="12">
              <v-autocomplete v-model="formData.requester_id" :items="users" item-title="name" item-value="id"
                item-subtitle="email" label="Selecione o Usuário*" :loading="loadingUsers"
                :rules="[v => !!v || 'Usuário é obrigatório']" required variant="outlined"
                density="comfortable"></v-autocomplete>
            </v-col>

            <!-- Título do Chamado -->
            <v-col cols="12">
              <v-text-field v-model="formData.title" label="Título do Chamado*"
                :rules="[v => !!v || 'Título é obrigatório']" required variant="outlined"
                density="comfortable"></v-text-field>
            </v-col>

            <!-- Prioridade e Setor na mesma linha -->
            <v-col cols="12" md="6">
              <v-select v-model="formData.priority" :items="priorityOptions" item-title="title" item-value="value"
                label="Prioridade*" required variant="outlined" density="comfortable"></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-select v-model="formData.sector_id" :items="sectors" item-title="name" item-value="id" label="Setor*"
                :loading="loadingSectors" :rules="[v => !!v || 'Setor é obrigatório']" required variant="outlined"
                density="comfortable"></v-select>
            </v-col>

            <!-- Descrição -->
            <v-col cols="12">
              <v-textarea v-model="formData.description" label="Descrição do Atendimento*"
                :rules="[v => !!v || 'Descrição é obrigatória']" required variant="outlined" rows="5" auto-grow
                density="comfortable"></v-textarea>
            </v-col>

            <!-- Upload de Arquivos -->
            <v-col cols="12">
              <v-file-input v-model="formData.files" label="Anexos (opcional)" multiple prepend-icon="mdi-paperclip"
                show-size truncate-length="15" accept=".pdf,.docx,.jpg,.jpeg,.png,.gif" :rules="[
                  files => !files.length || files.every(file => file.size < 10000000) || 'O tamanho do arquivo deve ser menor que 10MB'
                ]" variant="outlined" density="comfortable"></v-file-input>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="closeDialog" :disabled="loading">
          Cancelar
        </v-btn>
        <v-btn color="primary" @click="submitForm" :loading="loading" variant="elevated">
          Criar Atendimento
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '@/services/api';
import { attendantAuthService } from '@/services/attendant-auth.service'

const props = defineProps({
  modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'created']);

const showDialog = ref(props.modelValue);
const form = ref(null);
const loading = ref(false);
const loadingUsers = ref(false);
const loadingSectors = ref(false);
const users = ref([]);
const sectors = ref([]);

// Opciones de prioridad
const priorityOptions = [
  { title: 'Baixa', value: 'BAIXA' },
  { title: 'Normal', value: 'NORMAL' },
  { title: 'Alta', value: 'ALTA' },
  { title: 'Urgente', value: 'URGENTE' }
];

// Datos del formulario
const formData = ref({
  title: '',
  description: '',
  priority: 'NORMAL',
  sector_id: null,
  requester_id: null,
  files: []
});

// Observa cambios en la propiedad modelValue para actualizar el diálogo
watch(() => props.modelValue, (newValue) => {
  showDialog.value = newValue;
});

// Observa cambios en el diálogo para emitir eventos
watch(showDialog, (newValue) => {
  emit('update:modelValue', newValue);
});

// Carregar usuarios 
const loadUsers = async () => {
  loadingUsers.value = true;
  try {
    const response = await api.get('/users'); // Teste com 'users' no plural
    console.log('Resposta da API:', response); // Para debug
    if (response && response.data && response.data.success) {
      users.value = response.data.data || [];
    } else {
      console.error('Resposta inválida:', response);
      users.value = []; // Inicializa como array vazio em caso de erro
    }
  } catch (error) {
    console.error('Erro ao carregar usuários:', error);
    users.value = []; // Inicializa como array vazio em caso de erro
  } finally {
    loadingUsers.value = false;
  }
};

// Cargar sectores al montar el componente
const loadSectors = async () => {
  loadingSectors.value = true;
  try {
    const response = await api.get('/sectors');
    if (response.data.success) {
      sectors.value = response.data.data;
    }
  } catch (error) {
    console.error('Error al cargar sectores:', error);
  } finally {
    loadingSectors.value = false;
  }
};

// Cerrar el diálogo y resetear el formulario
const closeDialog = () => {
  showDialog.value = false;
  if (form.value) {
    form.value.reset();
  }
};

// Enviar el formulario
// No método submitForm
const submitForm = async () => {
  if (form.value) {
    const { valid } = await form.value.validate();
    if (!valid) return;
  }

  loading.value = true;

  try {
    // Criar FormData para envio com arquivos
    const submitData = new FormData();
    const attendant = attendantAuthService.getAttendantData();

    // Adicionar campos básicos
    submitData.append('title', formData.value.title);
    submitData.append('description', formData.value.description);
    submitData.append('priority', formData.value.priority);
    submitData.append('sector_id', formData.value.sector_id);
    submitData.append('requester_id', formData.value.requester_id);
    submitData.append('created_by_admin_id', attendant.id);
    submitData.append('created_by_admin', 'true');
    
    // Adicionar arquivos apenas se existirem
    if (formData.value.files && formData.value.files.length > 0) {
      const validFiles = formData.value.files.filter(file => file != null);
      validFiles.forEach((file) => {
        submitData.append('files[]', file);
      });
    }

    const response = await api.post('/service/admin/create', submitData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    console.log('Resposta do servidor:', response.data);
    
    // Verificar se a resposta contém código de status HTTP 201 (Created)
    if (response.status === 201) {
      // Se o status é 201, consideramos sucesso mesmo se não conseguirmos interpretar a resposta
      emit('created', { message: 'Atendimento criado com sucesso' });
      closeDialog();
      return;
    }
    
    // Tenta processar a resposta como JSON
    if (typeof response.data === 'object' && response.data.success) {
      emit('created', response.data.data);
      closeDialog();
    } else {
      // Se não conseguiu processar como JSON mas o status é positivo, consideramos sucesso
      if (response.status >= 200 && response.status < 300) {
        emit('created', { message: 'Atendimento criado com sucesso' });
        closeDialog();
      } else {
        throw new Error("Resposta inesperada do servidor");
      }
    }
  } catch (error) {
    console.error('Erro ao criar o atendimento:', error);
    console.error('Detalhes adicionais:', error.response?.data);
    
    alert('Erro ao criar o atendimento: ' + (error.response?.data?.message || error.message || 'Erro desconhecido'));
  } finally {
    loading.value = false;
  }
};

// Cargar datos al montar el componente
onMounted(() => {
  loadUsers();
  loadSectors();
});
</script>

<style scoped>
.v-card-title {
  font-size: 1.25rem;
  font-weight: 500;
}

.v-card-text {
  padding-top: 20px;
}
</style>