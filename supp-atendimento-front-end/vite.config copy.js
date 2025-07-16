// supp-atendimento-front-end/vite.config.js

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// 1. IMPORTAR O PLUGIN DO VUETIFY
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
  plugins: [
    vue(),
    // 2. USAR O PLUGIN AQUI
    vuetify({ autoImport: true }), 
  ],
  server: {
    host: '0.0.0.0', 
    strictPort: true, 
    port: 5173, 
    watch: {
      usePolling: true,
    },
    hmr: {
      protocol: 'ws',
      host: 'localhost',
    },
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
})