<template>
  <div>
    <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-2" />

    <v-timeline side="end" density="compact" class="mb-4">
      <v-timeline-item
        v-for="ev in evolutions"
        :key="ev.id"
        dot-color="primary"
        size="small"
      >
        <template #opposite>
          <span class="text-caption">{{ formatDate(ev.date_history) }}</span>
        </template>
        <v-card variant="outlined">
          <v-card-text class="pa-3">
            <div class="d-flex align-center mb-1">
              <v-icon size="small" class="mr-1">mdi-account</v-icon>
              <strong>{{ ev.responsible?.name }}</strong>
              <span class="text-caption text-grey ml-2">{{ formatDate(ev.date_history) }}</span>
            </div>
            <p class="text-body-2 mb-0">{{ ev.comment }}</p>
          </v-card-text>
        </v-card>
      </v-timeline-item>
    </v-timeline>

    <div v-if="evolutions.length === 0 && !loading" class="text-grey text-body-2 mb-3">
      Nenhuma evolução registrada.
    </div>

    <div v-if="canRegister">
      <v-divider class="mb-3" />
      <v-text-field
        v-model="newDate"
        label="Data da Atividade"
        type="date"
        class="mb-2"
      />
      <v-textarea
        v-model="newComment"
        label="Registrar Evolução"
        rows="3"
        placeholder="Descreva o que foi realizado..."
        class="mb-2"
      />
      <v-alert v-if="saveError" type="error" class="mb-2">{{ saveError }}</v-alert>
      <v-btn color="primary" :loading="saving" @click="registerEvolution" :disabled="!newComment.trim()">
        Registrar Evolução
      </v-btn>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { authService } from '@/services/auth.service'
import api from '@/services/api'

const props = defineProps({
  serviceId: { type: Number, required: true },
})

const evolutions = ref([])
const loading = ref(false)
const newComment = ref('')
const newDate = ref(new Date().toISOString().split('T')[0])
const saving = ref(false)
const saveError = ref(null)

const canRegister = computed(() => authService.isAttendant())

function formatDate(dt) {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

async function loadEvolutions() {
  loading.value = true
  try {
    const res = await api.get(`/service/${props.serviceId}/evolutions`)
    evolutions.value = res.data.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function registerEvolution() {
  saveError.value = null
  saving.value = true
  try {
    await api.post(`/service/${props.serviceId}/evolution`, {
      comment: newComment.value,
      date_activity: newDate.value,
    })
    newComment.value = ''
    newDate.value = new Date().toISOString().split('T')[0]
    await loadEvolutions()
  } catch (e) {
    saveError.value = e.response?.data?.message || e.message
  } finally {
    saving.value = false
  }
}

onMounted(loadEvolutions)
</script>
