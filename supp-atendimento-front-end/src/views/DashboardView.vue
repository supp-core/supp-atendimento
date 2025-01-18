<template>
    <v-container>
      <v-row>
        <v-col>
          <h1 class="text-h4 mb-4">Chamados</h1>
          
          <!-- Botão para criar novo chamado -->
          <v-btn
            color="primary"
            class="mb-4"
            @click="createTicket"
          >
            Novo Chamado
          </v-btn>
  
          <!-- Lista de chamados -->
          <v-card>
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Título</th>
                  <th>Status</th>
                  <th>Setor</th>
                  <th>Data de Criação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                  <td>#{{ ticket.id }}</td>
                  <td>{{ ticket.title }}</td>
                  <td>
                    <v-chip
                      :color="getStatusColor(ticket.status)"
                      text-color="white"
                    >
                      {{ ticket.status }}
                    </v-chip>
                  </td>
                  <td>{{ ticket.sector?.name }}</td>
                  <td>{{ formatDate(ticket.dates.created) }}</td>
                  <td>
                    <v-btn
                      icon
                      variant="text"
                      @click="viewTicket(ticket.id)"
                    >
                      <v-icon>mdi-eye</v-icon>
                    </v-btn>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { format } from 'date-fns'
  import { ptBR } from 'date-fns/locale'
  import { authService } from '@/services/auth.service'
  
  const router = useRouter()
  const tickets = ref([])
  
  // Função para formatar data
  const formatDate = (dateString) => {
    if (!dateString) return ''
    return format(new Date(dateString), "dd/MM/yyyy 'às' HH:mm", {
      locale: ptBR
    })
  }
  
  // Função para determinar cor do status
  const getStatusColor = (status) => {
    const colors = {
      'NEW': 'blue',
      'OPEN': 'orange',
      'IN_PROGRESS': 'purple',
      'RESOLVED': 'green',
      'CONCLUDED': 'grey'
    }
    return colors[status] || 'grey'
  }
  
  // Navegação para criar novo chamado
  const createTicket = () => {
    router.push('/tickets/create')
  }
  
  // Visualizar detalhes do chamado
  const viewTicket = (id) => {
    router.push(`/tickets/${id}`)
  }
  
  // Carregar lista de chamados
  onMounted(async () => {
    if (!authService.isAuthenticated()) {
      router.push('/login')
      return
    }
    
    try {
      // Aqui você fará a chamada para sua API
      // Por enquanto, usando dados mockados
      tickets.value = [
        {
          id: 1,
          title: 'Problema com impressora',
          status: 'NEW',
          sector: { name: 'Infraestrutura' },
          dates: { created: '2024-01-17T10:00:00' }
        },
        {
          id: 2,
          title: 'Erro no sistema',
          status: 'IN_PROGRESS',
          sector: { name: 'Desenvolvimento' },
          dates: { created: '2024-01-17T09:30:00' }
        }
      ]
    } catch (error) {
      console.error('Erro ao carregar chamados:', error)
    }
  })
  </script>