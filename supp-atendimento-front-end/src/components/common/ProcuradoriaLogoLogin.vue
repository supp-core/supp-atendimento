<template>
  <div class="procuradoria-logo" :style="{ width: width + 'px', height: height + 'px' }">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 200" width="100%" height="100%">
      <!-- Fundo transparente -->
      <rect width="600" height="200" fill="none"/>
      
      <!-- Texto principal com fontes maiores -->
      <text x="300" y="70" font-family="Palatino, 'Times New Roman', serif" font-size="36" font-weight="bold" text-anchor="middle" fill="#003366">PROCURADORIA</text>
      <text x="300" y="110" font-family="Palatino, 'Times New Roman', serif" font-size="30" text-anchor="middle" fill="#003366">DO MUNICÍPIO DE</text>
      <text x="300" y="150" font-family="Palatino, 'Times New Roman', serif" font-size="36" font-weight="bold" text-anchor="middle" fill="#003366">BELO HORIZONTE</text>
      
      <!-- Linhas decorativas mais longas e mais afastadas do texto -->
      <line x1="100" y1="185" x2="500" y2="185" stroke="#003366" stroke-width="4"/>
      <line x1="100" y1="30" x2="500" y2="30" stroke="#003366" stroke-width="4"/>
    </svg>
  </div>
</template>

<script>
export default {
  name: 'ProcuradoriaLogo',
  props: {
    width: {
      type: [Number, String],
      default: 600 // Tamanho largo mantido
    },
    height: {
      type: [Number, String],
      default: 220 // Ligeiramente aumentado para acomodar as linhas mais afastadas
    }
  }
}
</script>

<style scoped>
.procuradoria-logo {
  display: inline-block;
  max-width: 100%; /* Garante que a logo não extrapole o contêiner pai */
}

.procuradoria-logo svg {
  width: 100%;
  height: 100%;
}

/* Adicionando algumas regras de responsividade */
@media (max-width: 768px) {
  .procuradoria-logo {
    max-width: 450px;
  }
}

@media (max-width: 480px) {
  .procuradoria-logo {
    max-width: 320px;
  }
}
</style>