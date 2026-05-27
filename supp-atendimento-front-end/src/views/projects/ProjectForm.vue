<template>
  <v-dialog v-model="drawer" max-width="520" persistent>
    <v-card>
      <v-card-title class="pa-4 d-flex align-center">
        <span>{{ isEdit ? 'Editar Projeto' : 'Novo Projeto' }}</span>
        <v-spacer />
        <v-btn icon="mdi-close" variant="text" @click="close" />
      </v-card-title>
      <v-divider />
      <v-card-text class="pa-4">
        <v-alert v-if="error" type="error" class="mb-4">{{ error }}</v-alert>

        <v-text-field
          v-model="form.name"
          label="Nome *"
          :rules="[v => !!v || 'Nome é obrigatório']"
          class="mb-2"
        />
        <v-text-field
          v-model="form.acronym"
          label="Sigla *"
          :rules="[v => !!v || 'Sigla é obrigatória', v => v.length <= 20 || 'Máx. 20 caracteres']"
          @input="form.acronym = form.acronym.toUpperCase()"
          class="mb-2"
        />
        <v-textarea
          v-model="form.description"
          label="Descrição"
          rows="3"
          class="mb-2"
        />
        <v-select
          v-model="form.status"
          :items="['ATIVO', 'INATIVO', 'CONCLUIDO']"
          label="Status *"
          class="mb-2"
        />
        <v-text-field
          v-model="form.date_start"
          label="Data Início *"
          type="date"
          :rules="[v => !!v || 'Data início é obrigatória']"
          class="mb-2"
        />
        <v-text-field
          v-model="form.date_end"
          label="Data Fim"
          type="date"
          :rules="[dateEndRule]"
          class="mb-2"
        />
      </v-card-text>
      <v-divider />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn variant="outlined" @click="close">Cancelar</v-btn>
        <v-btn color="primary" :loading="loading" @click="save">Salvar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useProjectStore } from '@/stores/projectStore'

const props = defineProps({
  modelValue: Boolean,
  project: Object,
})
const emit = defineEmits(['update:modelValue', 'saved'])

const projectStore = useProjectStore()
const loading = ref(false)
const error = ref(null)

const drawer = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isEdit = computed(() => !!props.project?.id)

const emptyForm = () => ({
  name: '',
  acronym: '',
  description: '',
  status: 'ATIVO',
  date_start: '',
  date_end: '',
})

const form = ref(emptyForm())

watch(() => props.project, (p) => {
  if (p) {
    form.value = {
      name: p.name || '',
      acronym: p.acronym || '',
      description: p.description || '',
      status: p.status || 'ATIVO',
      date_start: p.date_start || '',
      date_end: p.date_end || '',
    }
  } else {
    form.value = emptyForm()
  }
}, { immediate: true })

const dateEndRule = (v) => {
  if (!v || !form.value.date_start) return true
  return v >= form.value.date_start || 'Data fim deve ser >= data início'
}

async function save() {
  error.value = null
  if (!form.value.name || !form.value.acronym || !form.value.date_start) {
    error.value = 'Preencha os campos obrigatórios (*).'
    return
  }
  loading.value = true
  try {
    const payload = { ...form.value }
    if (!payload.date_end) delete payload.date_end

    if (isEdit.value) {
      await projectStore.updateProject(props.project.id, payload)
    } else {
      await projectStore.createProject(payload)
    }
    emit('saved')
    close()
  } catch (e) {
    error.value = e.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}

function close() {
  error.value = null
  emit('update:modelValue', false)
}
</script>
