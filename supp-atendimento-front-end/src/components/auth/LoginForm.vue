<template>
  <div class="login-card">
    <!-- Header com logo modificado -->
    <div class="login-header">
      <div class="logo-wrapper mb-4">
        <ProcuradoriaLogoLogin width="320" height="120" />
      </div>
      <div class="portal-text">Portal do Usuário</div>
    </div>

    <div class="login-form">
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="email">Email<span class="required">*</span></label>
          <div class="input-with-icon">
            <input id="email" type="text" v-model="email" placeholder="Digite seu Email" class="form-input" required>
            <span class="input-icon">👤</span>
          </div>
          <p class="input-helper">Email é obrigatório</p>
        </div>

        <div class="form-group">
          <label for="password">Senha<span class="required">*</span></label>
          <div class="input-with-icon">
            <input id="password" :type="showPassword ? 'text' : 'password'" v-model="password"
              placeholder="Digite sua senha" class="form-input" required>
            <button type="button" class="password-toggle" @click="showPassword = !showPassword">
              <span class="eye-icon">👁</span>
            </button>
          </div>
        </div>

        <a href="#" class="forgot-password">Esqueceu a senha?</a>

        <button type="submit" class="login-button" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/auth.service'
import ProcuradoriaLogoLogin from '@/components/common/ProcuradoriaLogoLogin.vue'

const router = useRouter()
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''

  try {
    await authService.login(email.value, password.value)
    router.push('/dashboard')
  } catch (err) {
    error.value = err.message || 'Erro ao fazer login'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Estilo para o fundo escuro da página */
:deep(body), :deep(html) {
  background-color: #f0f2f5;
}

/* Novo estilo para o texto "Portal do Usuário" */
.portal-text {
  color: #1a237e;
  font-size: 1.2rem;
  font-weight: 500;
  text-align: center;
  margin-bottom: 1.5rem;
  letter-spacing: 0.5px;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  width: 400px;
  max-width: 90%;
  /* Fundo sutilmente sombreado para o card */
  background: linear-gradient(to bottom, #ffffff, #f9f9f9);
}

.login-header {
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-container {
  width: 280px;
  margin: 0 auto 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  color: #333;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.required {
  color: #f44336;
  margin-left: 2px;
}

.input-with-icon {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  padding-left: 2.5rem;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #1a237e;
  outline: none;
  box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.2);
}

.input-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
}

.input-helper {
  color: #f44336;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
}

.forgot-password {
  display: block;
  text-align: right;
  color: #1a237e;
  text-decoration: none;
  font-size: 0.875rem;
  margin: 1rem 0;
  transition: color 0.2s;
}

.forgot-password:hover {
  color: #0d47a1;
  text-decoration: underline;
}

.login-button {
  width: 100%;
  padding: 0.875rem;
  background-color: #1a237e;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.1s;
  font-weight: 500;
}

.login-button:hover {
  background-color: #0d47a1;
  transform: translateY(-1px);
}

.login-button:active {
  transform: translateY(1px);
}

.login-button:disabled {
  background-color: #9fa8da;
  cursor: not-allowed;
  transform: none;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #ffebee;
  color: #c62828;
  border-radius: 4px;
  text-align: center;
  font-size: 0.875rem;
}
</style>