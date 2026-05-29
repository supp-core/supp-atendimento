<template>
  <v-app>
    <v-main class="login-bg">
      <v-container fluid class="fill-height pa-4">
        <v-row justify="center" align="center" class="ma-0">
          <v-col cols="12" sm="11" md="8" lg="6" xl="5" class="pa-0">
            <v-card class="login-card" rounded="lg" elevation="2">

              <!-- Cabeçalho -->
              <div class="login-header">
                <p class="header-sub">Você está no</p>
                <p class="header-title">PGM Atendimento</p>
              </div>

              <!-- Corpo em duas colunas -->
              <div class="login-body">

                <!-- Coluna esquerda: branding -->
                <div class="login-brand">
                  <SuppLogo :width="110" :height="110" />
                  <p class="brand-name">PGM/PBH</p>
                  <p class="brand-env">ATENDIMENTO</p>
                </div>

                <div class="login-divider"></div>

                <!-- Coluna direita: formulário -->
                <div class="login-form-col">
                  <p class="form-title">Login</p>

                  <v-form ref="form" @submit.prevent="handleSubmit">
                    <v-text-field
                      v-model="usuario"
                      label="Usuário"
                      variant="outlined"
                      density="comfortable"
                      autocomplete="username"
                      :rules="[v => !!v || 'Usuário é obrigatório']"
                      class="mb-3"
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
                      class="mb-1"
                    />

                    <v-btn
                      type="submit"
                      size="large"
                      block
                      rounded="pill"
                      class="login-button mt-3"
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
                </div>

              </div>
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
  background-color: #eef0f4;
  min-height: 100vh;
}

.login-card {
  background: #ffffff;
  overflow: hidden;
}

/* Cabeçalho */
.login-header {
  text-align: center;
  padding: 28px 32px 20px;
  border-bottom: 1px solid #f0f0f0;
}

.header-sub {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
}

.header-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1a237e;
  margin: 2px 0 0;
}

/* Corpo */
.login-body {
  display: flex;
  align-items: center;
  padding: 40px 32px;
  gap: 0;
}

/* Branding esquerda */
.login-brand {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding-right: 32px;
}

.brand-name {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.brand-env {
  font-size: 0.8125rem;
  font-weight: 700;
  color: #6b7280;
  letter-spacing: 1.5px;
  margin: 0;
}

/* Divisor vertical */
.login-divider {
  width: 1px;
  height: 200px;
  background: #e5e7eb;
  flex-shrink: 0;
}

/* Formulário direita */
.login-form-col {
  flex: 1.2;
  padding-left: 36px;
}

.form-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 20px;
}

.login-button {
  background: #1a3a6b !important;
  color: #ffffff !important;
  text-transform: none;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.login-button:hover {
  background: #1a237e !important;
}

/* Responsivo: coluna única em mobile */
@media (max-width: 600px) {
  .login-body {
    flex-direction: column;
    padding: 32px 24px;
  }

  .login-brand {
    padding-right: 0;
    padding-bottom: 28px;
  }

  .login-divider {
    width: 80px;
    height: 1px;
    margin-bottom: 28px;
  }

  .login-form-col {
    padding-left: 0;
    width: 100%;
  }
}
</style>
