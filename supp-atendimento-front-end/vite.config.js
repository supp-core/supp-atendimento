// supp-atendimento-front-end/vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'

export default defineConfig({
  plugins: [
    vue(),
    vuetify({ autoImport: true }), 
  ],
  server: {
    host: '0.0.0.0', 
    strictPort: true, 
    port: 5173, 
    watch: {
      usePolling: true,
    },
    // hmr: {
    //   protocol: 'ws',
    //   host: 'localhost',
    // },
    // --- ESTA SEÇÃO É ESSENCIAL PARA A API FUNCIONAR ---
    proxy: {
      '/api': {
        target: 'http://backend:80',
        changeOrigin: true,
        // rewrite: (path) => path.replace(/^\/api/, ''),
      },
    }
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
})