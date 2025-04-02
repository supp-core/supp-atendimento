// Adicionar esta configuração ao seu vite.config.js existente
export default defineConfig({
    // suas configurações existentes...
    server: {
      host: '0.0.0.0',
      port: 5173,
      watch: {
        usePolling: true
      }
    }
  })
