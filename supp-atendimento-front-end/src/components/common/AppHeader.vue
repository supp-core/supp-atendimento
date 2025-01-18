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
    position: fixed; /* Fixa o cabeçalho no topo */
  top: 0;
  left: 0;
  right: 0; /* Garante que ocupe toda a largura */
  background: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  z-index: 1000; /* Garante que fique acima dos outros elementos */
  height: 60px; /* Altura fixa para alinhar com a sidebar */
  }
  
  .logo-img {
    height: 40px;
    width: auto;
  }


  .dashboard {
  padding-top: 60px; /* Mesma altura do header */
  min-height: 100vh;
  background-color: #f3f4f6;
}

  </style>
  