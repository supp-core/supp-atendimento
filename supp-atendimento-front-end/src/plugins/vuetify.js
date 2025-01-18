import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'  // Adicione esta linha se não existir
import { createVuetify } from 'vuetify'

export default createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#6044ec',
          secondary: '#2196F3',
          background: '#f5f5f5',
          surface: '#FFFFFF',
          error: '#FF5252',
          success: '#4CAF50',
          warning: '#FFC107',
          'primary-darken-1': '#3700B3',
          'grey-lighten-1': '#f8f9fa'
        }
      }
    }
  },
  icons: {
    defaultSet: 'mdi'  // Adicione esta configuração
  }
})