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
  width: 100vw; /* Garante que ocupe toda a largura */
  padding: 1rem;
  box-sizing: border-box;
}


.parent-container > div {
  position: relative;
  transform: translateY(-20px); /* Move o formulário um pouco para cima */
}
</style>
