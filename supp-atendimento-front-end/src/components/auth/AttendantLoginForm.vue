<template>
    <div class="login-card">
      <div class="login-header">
        <div class="logo-text-container">
          <div class="logo-wrapper">
            <img src="/assets/supp.png" alt="SUPP/PBH" class="logo">
          </div>
          <div class="text-wrapper">
            <p class="supp-text">SUPP/PBH</p>
            <p class="environment">Portal do Atendente</p>
          </div>
        </div>
      </div>
  
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="email">Email</label>
          <input 
            type="email" 
            v-model="email" 
            required 
            class="form-input"
          >
        </div>
  
        <div class="form-group">
          <label for="password">Senha</label>
          <input 
            :type="showPassword ? 'text' : 'password'" 
            v-model="password" 
            required 
            class="form-input"
          >
        </div>
  
        <button type="submit" class="login-button" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useRouter } from 'vue-router'
  import { attendantAuthService } from '@/services/attendant-auth.service'
  
  const router = useRouter()
  const email = ref('')
  const password = ref('')
  const loading = ref(false)
  const error = ref('')
  
  const handleSubmit = async () => {
    loading.value = true
    try {
      await attendantAuthService.login(email.value, password.value)
      router.push('/attendant/dashboard')
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }
  </script>