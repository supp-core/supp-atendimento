<template>
  <div class="unified-login-container">
    <UnifiedLoginForm />
  </div>
</template>

<script setup>
import UnifiedLoginForm from '@/components/auth/UnifiedLoginForm.vue'
</script>

<style scoped>
.unified-login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
}
</style>