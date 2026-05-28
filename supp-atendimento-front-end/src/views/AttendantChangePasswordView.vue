<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="change-password-page">
          <h2 class="text-h5 font-weight-medium mb-4">Alterar Senha</h2>
          
          <v-card class="change-password-card">
            <v-card-text>
              <v-form @submit.prevent="handleSubmit" ref="form">
                <v-text-field
                  v-model="currentPassword"
                  label="Senha Atual"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  :rules="[v => !!v || 'Senha atual é obrigatória']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-lock"
                  :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showCurrentPassword = !showCurrentPassword"
                  class="mb-4"
                ></v-text-field>

                <v-text-field
                  v-model="newPassword"
                  label="Nova Senha"
                  :type="showNewPassword ? 'text' : 'password'"
                  :rules="[
                    v => !!v || 'Nova senha é obrigatória',
                    v => v.length >= 6 || 'Nova senha deve ter pelo menos 6 caracteres'
                  ]"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-lock-plus"
                  :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showNewPassword = !showNewPassword"
                  class="mb-4"
                ></v-text-field>

                <v-text-field
                  v-model="confirmPassword"
                  label="Confirmar Nova Senha"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  :rules="[
                    v => !!v || 'Confirmação de senha é obrigatória',
                    v => v === newPassword || 'Senhas não coincidem'
                  ]"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-lock-check"
                  :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showConfirmPassword = !showConfirmPassword"
                  class="mb-6"
                ></v-text-field>

                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  :loading="loading"
                  class="me-2 btn-centered"
                >
                  {{ loading ? 'Alterando...' : 'Alterar Senha' }}
                </v-btn>

                <v-btn
                  variant="outlined"
                  @click="cancelChange"
                  :disabled="loading"
                  class="btn-centered"
                >
                  Cancelar
                </v-btn>

                <v-alert
                  v-if="error"
                  type="error"
                  variant="tonal"
                  closable
                  class="mt-4"
                >
                  {{ error }}
                </v-alert>

                <v-alert
                  v-if="success"
                  type="success"
                  variant="tonal"
                  closable
                  class="mt-4"
                >
                  {{ success }}
                </v-alert>
              </v-form>
            </v-card-text>
          </v-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AttendantHeader from '@/components/common/AttendantHeader.vue'
import AttendantSidebar from '@/components/common/AttendantSidebar.vue'
import { useSidebar } from '@/composables/useSidebar'
import api from '@/services/api'

const router = useRouter()
const { sidebarCollapsed } = useSidebar()

const form = ref(null)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref('')
const success = ref('')

const handleSubmit = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) {
    error.value = 'Por favor, preencha todos os campos corretamente'
    return
  }
  
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const response = await api.put('/attendant/change-password', {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: confirmPassword.value
    })

    if (response.data.success) {
      success.value = 'Senha alterada com sucesso!'
      
      // Limpar campos
      currentPassword.value = ''
      newPassword.value = ''
      confirmPassword.value = ''
      
      // Redirecionar após 2 segundos
      setTimeout(() => {
        router.push('/attendant/tickets')
      }, 2000)
    } else {
      error.value = response.data.message || 'Erro ao alterar senha'
    }
  } catch (err) {
    console.error('Erro ao alterar senha:', err)
    error.value = err.response?.data?.message || 'Erro ao alterar senha. Verifique sua senha atual.'
  } finally {
    loading.value = false
  }
}

const cancelChange = () => {
  router.push('/attendant/tickets')
}
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

.change-password-page {
  max-width: 600px;
  margin: 0 auto;
}

.change-password-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  padding: 24px;
}

:deep(.v-field) {
  border-radius: 8px !important;
}

:deep(.v-btn) {
  text-transform: none !important;
  font-weight: 500 !important;
  letter-spacing: 0.5px !important;
  text-transform: uppercase !important; 
}

.btn-centered {
  text-align: center !important;
}

.btn-centered :deep(.v-btn__content) {
  justify-content: center !important;
  text-align: center !important;
  width: 100% !important;
  display: flex !important;
}
</style>