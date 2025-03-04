<template>
  <v-dialog v-model="dialogVisible" max-width="900px">
    <v-card>

      <!-- Cabeçalho do Modal -->
      <v-card-title class="d-flex justify-space-between align-center pa-4">
        <div>
          <h3 class="text-h5 mb-1">{{ ticket?.title }}</h3>
          <span class="text-caption">Protocolo #{{ ticket?.id }}</span>
        </div>
        <v-chip :color="getPriorityColor(ticket?.priority)" text-color="white" size="small">
          {{ ticket?.priority }}
        </v-chip>
      </v-card-title>

      <v-divider></v-divider>

      <!-- Detalhes do Atendimento -->
      <v-card-text class="pa-4">
        <div class="description-container mb-4">
          <div class="description-label mb-2">
            <v-icon icon="mdi-text-box-outline" class="mr-2"></v-icon>
            Descrição do Atendimento
          </div>
          <div class="description-content">
            {{ ticket?.description }}
          </div>
        </div>

        <v-row>
          <v-col cols="6">
            <div class="metadata-item">
              <v-icon size="small" class="mr-1">mdi-calendar</v-icon>
              <span class="metadata-label">Aberto em:</span>
              {{ formatDate(ticket?.dates?.created) }}
            </div>
          </v-col>
          <v-col cols="6">
            <div class="metadata-item">
              <v-icon size="small" class="mr-1">mdi-domain</v-icon>
              <span class="metadata-label">Setor:</span>
              {{ ticket?.sector?.name }}
            </div>
          </v-col>
          <v-col cols="6">
            <div class="metadata-item">
              <v-icon size="small" class="mr-1">mdi-flag</v-icon>
              <span class="metadata-label">Status:</span>
              <v-chip :color="getStatusColor(ticket?.status)" text-color="white" size="x-small" class="ml-2">
                {{ translateStatus(ticket?.status) }}
              </v-chip>
            </div>
          </v-col>
          <v-col cols="6">
            <div class="metadata-item">
              <v-icon size="small" class="mr-1">mdi-account</v-icon>
              <span class="metadata-label">Responsável:</span>
              {{ ticket?.responsible?.name || 'Não atribuído' }}
            </div>
          </v-col>
        </v-row>
      </v-card-text>

      <v-divider></v-divider>

      <!-- Timeline/Histórico -->
      <v-card-text class="pa-4">
        <h4 class="text-h6 mb-4">Histórico</h4>
        <div class="timeline">
          <div v-for="(history, index) in ticket?.histories" :key="index" class="timeline-item">
            <div class="timeline-header d-flex align-center">
              <v-avatar size="32" class="mr-3">
                <v-img src="/assets/user-avatar.png"></v-img>
              </v-avatar>
              <div>
                <span class="font-weight-medium">{{ history.responsible?.name }}</span>
                <span class="text-caption ml-2 grey--text">
                  {{ formatDate(history.date) }}
                </span>
              </div>
            </div>

            <div class="timeline-content ml-11">
              <div class="d-flex align-center gap-2 mb-2">
                <v-chip :color="getStatusColor(history.status_prev)" size="x-small" text-color="white">
                  {{ translateStatus(history.status_prev) }}
                </v-chip>
                <v-icon>mdi-arrow-right</v-icon>
                <v-chip :color="getStatusColor(history.status_post)" size="x-small" text-color="white">
                  {{ translateStatus(history.status_post) }}
                </v-chip>
              </div>
              <p class="text-body-2 mb-0">{{ history.comment }}</p>
            </div>
          </div>
        </div>
      </v-card-text>

      <!-- Anexos - Corrigido -->
      <template v-if="ticket?.attachments && ticket.attachments.length > 0">
        <v-divider></v-divider>
        <v-card-text class="pa-4">
          <h4 class="text-h6 mb-4">Anexos</h4>
          <v-list>
            <v-list-item v-for="attachment in ticket.attachments" :key="attachment.id" class="px-2">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-document-outline"></v-icon>
              </template>

              <v-list-item-title>{{ attachment.originalFilename }}</v-list-item-title>

              <template v-slot:append>
                <v-btn color="primary" variant="text" size="small" @click="downloadAttachment(attachment)"
                  :loading="loading">
                  <v-icon left>mdi-download</v-icon>
                  Download
                </v-btn>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
      </template>

      <v-snackbar v-model="feedback.show" :color="feedback.type" timeout="3000">
        {{ feedback.message }}
        <template v-slot:actions>
          <v-btn variant="text" @click="feedback.show = false">
            Fechar
          </v-btn>
        </template>
      </v-snackbar>

      <!-- Ações -->
      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="closeDialog">
          Fechar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { ticketsService } from '@/services/tickets.service';

const props = defineProps({
  modelValue: Boolean,
  ticket: Object
})


const emit = defineEmits(['update:modelValue'])

const dialogVisible = ref(props.modelValue)
const loading = ref(false) // Adicionado estado de loading

// Feedback para notificações
const feedback = ref({
  show: false,
  message: '',
  type: 'success'
});

watch(() => props.modelValue, (newValue) => {
  dialogVisible.value = newValue
})

watch(dialogVisible, (newValue) => {
  emit('update:modelValue', newValue)
})

const getPriorityColor = (priority) => {
  const colors = {
    'URGENTE': 'red',
    'ALTA': 'orange',
    'NORMAL': 'blue',
    'BAIXA': 'green'
  }
  return colors[priority] || 'grey'
}

const getStatusColor = (status) => {
  const colors = {
    'NEW': 'grey',
    'OPEN': 'blue',
    'IN_PROGRESS': 'orange',
    'RESOLVED': 'green',
    'CONCLUDED': 'purple'
  }
  return colors[status] || 'grey'
}

const translateStatus = (status) => {
  const translations = {
    'NEW': 'Novo',
    'OPEN': 'Aberto',
    'IN_PROGRESS': 'Em Andamento',
    'RESOLVED': 'Resolvido',
    'CONCLUDED': 'Concluído'
  }
  return translations[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
    locale: ptBR
  })
}

const closeDialog = () => {
  dialogVisible.value = false
}

const downloadAttachment = async (attachment) => {
  try {

    console.log('attachment=====>', attachment);

    // Mostrar indicador de carregamento
    loading.value = true;

    // Fazer a requisição de download
    const blob = await ticketsService.downloadAttachment(attachment.id);

    // Criar URL objeto temporário para o blob
    const url = window.URL.createObjectURL(blob);

    // Criar elemento de link para acionar o download
    const link = document.createElement('a');
    link.href = url;
    link.download = attachment.originalFilename;

    // Adicionar à página, clicar e remover
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Liberar o objeto URL
    window.URL.revokeObjectURL(url);
  } catch (error) {
    // Exibir notificação de erro
    feedback.value = {
      show: true,
      message: 'Erro ao baixar o anexo',
      type: 'error'
    };
    console.error('Erro ao baixar anexo:', error);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.timeline {
  position: relative;
  padding-top: 16px;
}

.timeline-item {
  position: relative;
  padding-bottom: 24px;
  border-left: 2px solid #e0e0e0;
  margin-left: 16px;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-header {
  margin-left: -16px;
  margin-bottom: 8px;
}

.description-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
}

.description-label {
  font-weight: 500;
  color: #1a237e;
  display: flex;
  align-items: center;
}

.description-content {
  white-space: pre-wrap;
  color: #333;
}

.metadata-item {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.metadata-label {
  font-weight: 500;
  margin-right: 8px;
  color: #666;
}
</style>