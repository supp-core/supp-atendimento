<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Você está no SUPP/PBH</h1>
        <p class="environment">PRODUÇÃO</p>
        <p class="version">1.19.5 f/1.19.0b</p>
      </div>

      <h2>Login de Usuário com CPF</h2>

      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label>CPF</label>
          <input
            type="text"
            v-model="cpf"
            placeholder="Digite seu CPF"
            class="form-input"
          >
        </div>

        <div class="form-group">
          <label>Senha</label>
          <div class="password-field">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              placeholder="Digite sua senha"
              class="form-input"
            >
            <button 
              type="button"
              class="password-toggle"
              @click="showPassword = !showPassword"
            >
              <span class="eye-icon">👁</span>
            </button>
          </div>
        </div>

        <a href="#" class="forgot-password">Esqueceu a senha?</a>

        <button type="submit" class="login-button" :disabled="loading">
          {{ loading ? 'Carregando...' : 'Entrar' }}
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

const router = useRouter()
const cpf = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    await authService.login(cpf.value, password.value)
    router.push('/tickets')
  } catch (err) {
    error.value = err.message || 'Erro ao fazer login'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 1rem;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 4px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 1.25rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 0.5rem;
}

.environment {
  color: #666;
  margin: 0;
  font-size: 1rem;
}

.version {
  color: #888;
  margin: 0;
  font-size: 0.875rem;
}

h2 {
  font-size: 1.125rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  color: #666;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: #fff9c4;
  font-size: 0.875rem;
}

.password-field {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  color: #666;
}

.eye-icon {
  font-size: 1rem;
  opacity: 0.6;
}

.forgot-password {
  display: block;
  text-align: right;
  color: #2196f3;
  text-decoration: none;
  font-size: 0.875rem;
  margin: 1rem 0;
}

.login-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #2196f3;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.login-button:hover {
  background-color: #1976d2;
}

.login-button:disabled {
  background-color: #90caf9;
  cursor: not-allowed;
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