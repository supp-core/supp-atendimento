<template>
  <header class="header">
    <div class="logo">
      <ProcuradoriaLogoCabecalho :width="140" :height="40" color="#1a237e" />    </div>
    <div class="user-actions">
      <span class="username">{{ nomeUsuario }}</span>
      <button @click="fazerLogout" class="logout-button">
        Sair
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '@/services/auth.service'
import ProcuradoriaLogoCabecalho from '@/components/common/ProcuradoriaLogoCabecalho.vue'
const router = useRouter()
const nomeUsuario = ref('')

const fazerLogout = async () => {
    try {
        await authService.logout()
        window.location.href = '/login'
    } catch (error) {
        console.error('Erro no logout:', error)
        window.location.href = '/login'
    }
}

onMounted(() => {
  const usuario = authService.getUser()
    if (usuario) {
        nomeUsuario.value = usuario.name || 'Usuário'
    } else {
        console.warn('Nenhum usuário encontrado no localStorage')
        nomeUsuario.value = 'Usuário'
    }
})
</script>

<style scoped>
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: white;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
}

.user-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.username {
    font-weight: 500;
    color: #333;
}

.logout-button {
    padding: 8px 16px;
    background-color: #1a237e;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.logout-button:hover {
    background-color: #0d47a1;
}
</style>