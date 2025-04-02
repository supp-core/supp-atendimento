// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
// Remova a importação incorreta do vuetify
// import vuetify from 'vuetify'

export default defineConfig({
  plugins: [
    vue(),
  ],
  server: {
    host: '0.0.0.0',  // Expõe para todas as interfaces de rede
    port: 5173        // Porta desejada
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Se você estiver usando SCSS
        additionalData: `@import "vuetify/styles";`,
      },
    },
  },
  // Adicione esta configuração para garantir que o Vuetify seja carregado corretamente
  build: {
    transpile: ['vuetify'],
  },
})