<template>
    <div class="procuradoria-logo">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 200">
        <!-- Fundo transparente -->
        <rect width="600" height="200" fill="none"/>
        
        <!-- Texto principal -->
        <text x="300" y="70" font-family="Palatino, 'Times New Roman', serif" font-size="28" font-weight="bold" text-anchor="middle" fill="#003366">PROCURADORIA</text>
        <text x="300" y="110" font-family="Palatino, 'Times New Roman', serif" font-size="24" text-anchor="middle" fill="#003366">DO MUNICÍPIO DE</text>
        <text x="300" y="150" font-family="Palatino, 'Times New Roman', serif" font-size="28" font-weight="bold" text-anchor="middle" fill="#003366">BELO HORIZONTE</text>
        
        <!-- Elementos decorativos -->
        <line x1="150" y1="170" x2="450" y2="170" stroke="#003366" stroke-width="2"/>
        <line x1="190" y1="40" x2="410" y2="40" stroke="#003366" stroke-width="2"/>
      </svg>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ProcuradoriaLogo',
    props: {
      width: {
        type: [Number, String],
        default: 300
      },
      height: {
        type: [Number, String],
        default: 100
      }
    }
  }
  </script>
  
  <style scoped>
  .procuradoria-logo {
    display: inline-block;
  }
  .procuradoria-logo svg {
    width: 100%;
    height: 100%;
  }
  </style>