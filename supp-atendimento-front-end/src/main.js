// main.js
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Importações do Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#6044ec',      // Cor principal (roxo/azul)
          secondary: '#2196F3',    // Azul secundário
          background: '#f5f5f5',   // Fundo cinza claro
          surface: '#FFFFFF',      // Superfícies brancas
          error: '#FF5252',        // Vermelho para erros
          success: '#4CAF50',      // Verde para sucesso
          warning: '#FFC107',      // Amarelo para avisos
          'primary-darken-1': '#3700B3',
          'grey-lighten-1': '#f8f9fa'
        }
      }
    }
  }
})

const app = createApp(App)
app.use(router)
app.use(vuetify)
app.mount('#app')