<template>
  <div>
    <div class="d-flex align-center mb-3">
      <span class="text-subtitle-1 font-weight-medium">Equipe da Demanda</span>
      <v-spacer />
      <v-btn v-if="canManage" size="small" color="primary" prepend-icon="mdi-account-plus" @click="addDialog = true">
        Adicionar Atendente
      </v-btn>
    </div>

    <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-2" />

    <div v-if="attendants.length === 0 && !loading" class="text-grey text-body-2 mb-2">
      Nenhum atendente adicional vinculado.
    </div>

    <div class="d-flex flex-wrap gap-2">
      <v-chip
        v-for="sa in attendants"
        :key="sa.id"
        closable
        :close-icon="isAdmin ? 'mdi-close' : undefined"
        :close-label="isAdmin ? 'Remover' : undefined"
        @click:close="isAdmin ? removeAttendant(sa) : null"
        color="primary"
        variant="outlined"
      >
        <v-avatar start>{{ initials(sa.attendant.name) }}</v-avatar>
        {{ sa.attendant.name }}
        <span class="text-caption ml-1">({{ sa.attendant.function }})</span>
      </v-chip>
    </div>

    <!-- Dialog para adicionar atendente -->
    <v-dialog v-model="addDialog" max-width="400">
      <v-card>
        <v-card-title>Adicionar Atendente</v-card-title>
        <v-card-text>
          <v-autocomplete
            v-model="selectedAttendantId"
            :items="availableAttendants"
            item-title="name"
            item-value="id"
            label="Selecionar atendente"
          />
          <v-alert v-if="addError" type="error" class="mt-2">{{ addError }}</v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="addDialog = false">Cancelar</v-btn>
          <v-btn color="primary" :loading="adding" @click="addAttendant">Adicionar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { authService } from '@/services/auth.service'
import api from '@/services/api'

const props = defineProps({
  serviceId: { type: Number, required: true },
})

const attendants = ref([])
const loading = ref(false)
const addDialog = ref(false)
const selectedAttendantId = ref(null)
const adding = ref(false)
const addError = ref(null)
const allAttendants = ref([])

const isAdmin = computed(() => authService.isAdmin())
const canManage = computed(() => authService.isAttendant())

const availableAttendants = computed(() => {
  const linked = attendants.value.map(sa => sa.attendant.id)
  return allAttendants.value.filter(a => !linked.includes(a.id))
})

function initials(name) {
  return (name || '').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

async function loadAttendants() {
  loading.value = true
  try {
    const res = await api.get(`/service/${props.serviceId}/attendants`)
    attendants.value = res.data.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadAllAttendants() {
  try {
    const res = await api.get('/attendants')
    allAttendants.value = res.data.data || []
  } catch (e) {
    console.error(e)
  }
}

async function addAttendant() {
  if (!selectedAttendantId.value) return
  addError.value = null
  adding.value = true
  try {
    await api.post(`/service/${props.serviceId}/attendants`, {
      attendant_id: selectedAttendantId.value,
    })
    await loadAttendants()
    addDialog.value = false
    selectedAttendantId.value = null
  } catch (e) {
    addError.value = e.response?.data?.message || e.message
  } finally {
    adding.value = false
  }
}

async function removeAttendant(sa) {
  try {
    await api.delete(`/service/${props.serviceId}/attendants/${sa.attendant.id}`)
    await loadAttendants()
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  loadAttendants()
  loadAllAttendants()
})
</script>
