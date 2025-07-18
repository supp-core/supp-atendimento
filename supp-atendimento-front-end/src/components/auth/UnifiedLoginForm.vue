<template>
  <v-container fluid class="fill-height login-container">
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="6" lg="4">
        <v-card class="login-card elevation-3">
          <v-card-item>
            <div class="d-flex flex-column align-center mb-4">
              <!-- Logo com tamanho aumentado -->
              <div class="logo-wrapper mb-4">
                <ProcuradoriaLogoLogin width="400" height="150" />
              </div>
              <div class="text-center">
                <v-card-subtitle class="text-subtitle-1 pa-0">
                  Portal de Acesso
                </v-card-subtitle>
              </div>
            </div>

            <v-form @submit.prevent="handleSubmit" ref="form">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                :rules="[
                  v => !!v || 'Email é obrigatório',
                  v => /.+@.+\..+/.test(v) || 'Email deve ser válido'
                ]"
                required
                variant="outlined"
                prepend-inner-icon="mdi-email"
                class="mb-4"
              ></v-text-field>

              <v-text-field
                v-model="password"
                label="Senha"
                :type="showPassword ? 'text' : 'password'"
                :rules="[v => !!v || 'Senha é obrigatória']"
                required
                variant="outlined"
                prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword"
                class="mb-6"
              ></v-text-field>


              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                :loading="loading"
                class="mb-4 btn-centered"
              >
                {{ loading ? 'Entrando...' : 'Entrar' }}
              </v-btn>

              <v-alert
                v-if="error"
                type="error"
                variant="tonal"
                closable
                class="mb-4"
              >
                {{ error }}
              </v-alert>
            </v-form>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/auth.service'
import { attendantAuthService } from '@/services/attendant-auth.service'
import ProcuradoriaLogoLogin from '@/components/common/ProcuradoriaLogoLogin.vue'

const router = useRouter()
const form = ref(null)
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  const { valid } = await form.value.validate()
  
  if (!valid) {
    error.value = 'Por favor, preencha todos os campos corretamente'
    return
  }
  
  loading.value = true
  error.value = ''

  try {
    // Primeiro tenta login como atendente
    await attendantAuthService.login(email.value, password.value)
    router.push('/attendant/tickets')
  } catch (err) {
    // Se falhar como atendente, tenta como usuário comum
    try {
      await authService.login(email.value, password.value)
      router.push('/tickets')
    } catch (secondErr) {
      // Se ambos falharam, mostrar erro
      error.value = 'Email ou senha incorretos, ou usuário não encontrado'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.login-card {
  border-radius: 8px !important;
  padding: 20px;
}

.logo-wrapper {
  width: 400px;
  height: 150px;
  margin: 0 auto;
}


:deep(.v-field) {
  border-radius: 8px !important;
}

:deep(.v-btn) {
  text-transform: none !important;
  font-weight: 500 !important;
  letter-spacing: 0.5px !important;
}

/* Centralização do texto dos botões */
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