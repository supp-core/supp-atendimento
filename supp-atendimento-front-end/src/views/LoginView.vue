<template>
  <v-app>
    <v-main class="login-bg">
      <v-container fluid class="fill-height pa-4">
        <v-row justify="center" align="center" class="ma-0">
          <v-col cols="12" sm="10" md="6" lg="4" xl="3" class="pa-0">
            <v-card class="login-card pa-8" rounded="lg" elevation="3">
              <div class="d-flex flex-column align-center mb-2">
                <SuppLogo :width="84" :height="84" />
                <h1 class="login-title mt-4">SUPP Atendimentos</h1>
                <p class="login-subtitle">Sistema de Atendimento</p>
              </div>

              <h2 class="login-heading mb-4">Entrar</h2>

              <v-form ref="form" @submit.prevent="handleSubmit">
                <v-text-field
                  v-model="usuario"
                  label="Usuário"
                  variant="outlined"
                  density="comfortable"
                  autocomplete="username"
                  :rules="[v => !!v || 'Usuário é obrigatório']"
                  class="mb-2"
                />

                <v-text-field
                  v-model="senha"
                  label="Senha"
                  variant="outlined"
                  density="comfortable"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showPassword = !showPassword"
                  :rules="[v => !!v || 'Senha é obrigatória']"
                  class="mb-2"
                />

                <v-btn
                  type="submit"
                  color="login-cta"
                  size="large"
                  block
                  rounded="pill"
                  class="login-button mt-4"
                  :loading="loading"
                >
                  Entrar
                </v-btn>

                <v-alert
                  v-if="error"
                  type="error"
                  variant="tonal"
                  density="compact"
                  class="mt-4"
                >
                  {{ error }}
                </v-alert>
              </v-form>

              <p class="login-footer mt-6">Autenticação via SUPP — PGM/PBH</p>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/auth.service'
import SuppLogo from '@/components/common/SuppLogo.vue'

const router = useRouter()
const form = ref(null)
const usuario = ref('')
const senha = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  const { valid } = await form.value.validate()
  if (!valid) return

  loading.value = true
  error.value = ''
  try {
    const { type } = await authService.login(usuario.value, senha.value)
    router.push(type === 'attendant' ? '/attendant/dashboard' : '/dashboard')
  } catch (err) {
    error.value = err.message || 'Erro ao fazer login'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-bg {
  background-color: #f5f6f8;
  min-height: 100vh;
}

.login-card {
  background: #ffffff;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  letter-spacing: 0.2px;
  margin: 0;
}

.login-subtitle {
  color: #8a8a8a;
  font-size: 0.875rem;
  margin: 4px 0 0 0;
}

.login-heading {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
}

.login-button {
  text-transform: none;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.login-footer {
  text-align: center;
  color: #8a8a8a;
  font-size: 0.8125rem;
  margin: 0;
}
</style>
