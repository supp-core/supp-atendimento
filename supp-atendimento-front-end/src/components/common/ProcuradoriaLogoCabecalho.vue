<template>
    <div class="logo-container" :style="{ width: width + 'px', height: height + 'px' }">
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        viewBox="0 0 600 200" 
        class="logo-svg"
        :width="width"
        :height="height"
      >
        <rect width="600" height="200" fill="none"/>
        <text 
          x="300" 
          y="115" 
          font-family="Palatino, 'Times New Roman', serif" 
          font-size="60" 
          font-weight="bold" 
          text-anchor="middle" 
          :fill="color"
        >
          PGM/PBH
        </text>
        <line x1="150" y1="150" x2="450" y2="150" :stroke="color" stroke-width="3"/>
        <line x1="150" y1="70" x2="450" y2="70" :stroke="color" stroke-width="3"/>
      </svg>
    </div>
  </template>
  
  <script>
  export default {
    name: 'PgmPbhLogo',
    props: {
      width: {
        type: [Number, String],
        default: 140
      },
      height: {
        type: [Number, String],
        default: 40
      },
      color: {
        type: String,
        default: '#003366'
      }
    }
  }
  </script>
  
  <style scoped>
  .logo-container {
    display: inline-block;
  }
  
  .logo-svg {
    width: 100%;
    height: 100%;
  }
  </style>