import { createVuetify } from 'vuetify'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

export default createVuetify({
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