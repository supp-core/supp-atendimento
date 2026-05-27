<template>
  <v-card class="mb-4">
    <v-card-text>
      <v-row>
        <v-col cols="12" sm="3">
          <v-text-field v-model="filters.date_from" label="Data Início *" type="date" />
        </v-col>
        <v-col cols="12" sm="3">
          <v-text-field v-model="filters.date_to" label="Data Fim *" type="date" />
        </v-col>
        <v-col cols="12" sm="3">
          <v-autocomplete
            v-model="filters.project_id"
            :items="projects"
            :item-title="p => `[${p.acronym}] ${p.name}`"
            item-value="id"
            label="Projeto (opcional)"
            clearable
          />
        </v-col>
        <v-col v-if="isAdmin" cols="12" sm="3">
          <v-autocomplete
            v-model="filters.attendant_id"
            :items="attendants"
            item-title="name"
            item-value="id"
            label="Atendente (opcional)"
            clearable
          />
        </v-col>
        <v-col cols="12" class="d-flex align-center">
          <v-alert v-if="formError" type="error" density="compact" class="mr-4">{{ formError }}</v-alert>
          <v-btn color="primary" :disabled="!filters.date_from || !filters.date_to" @click="generate">
            Gerar Relatório
          </v-btn>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { authService } from '@/services/auth.service'
import api from '@/services/api'

const emit = defineEmits(['generate'])
const isAdmin = computed(() => authService.isAdmin())

const projects = ref([])
const attendants = ref([])
const formError = ref(null)

const filters = ref({
  date_from: null,
  date_to: null,
  project_id: null,
  attendant_id: null,
})

function generate() {
  formError.value = null
  if (!filters.value.date_from || !filters.value.date_to) {
    formError.value = 'Período obrigatório.'
    return
  }
  emit('generate', { ...filters.value })
}

onMounted(async () => {
  try {
    const [pRes, aRes] = await Promise.all([
      api.get('/project'),
      api.get('/attendants'),
    ])
    projects.value = pRes.data.data || []
    attendants.value = aRes.data.data || []
  } catch (e) {
    console.error(e)
  }
})
</script>
