<template>
    <header class="header">
      <div class="logo">
        <img src="/assets/supp.png" alt="SUPP/PBH" class="logo-img">
      </div>
      <div class="user-info">
        <span>{{ username }}</span>
      </div>
    </header>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const username = ref('Usuário');
  </script>
  
  <style scoped>
  .header {
    background: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  
  .logo-img {
    height: 40px;
    width: auto;
  }
  </style>