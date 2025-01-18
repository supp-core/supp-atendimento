<template>
  <div class="parent-container">
  <LoginForm />
</div>
</template>

<script setup>
import LoginForm from '@/components/auth/LoginForm.vue'
</script>

<style scoped>
.parent-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh; /* Garante que o container ocupe toda a altura da tela */
  padding: 1rem;
  box-sizing: border-box;
  position: relative; /* Permite ajustes de posicionamento */
}

.parent-container > div {
  position: relative;
  left: 300px;
 /* top: -150px; /* Move o formulário 20px para cima */
}
</style>
