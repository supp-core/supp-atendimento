<template>
  <div class="dashboard">
    <AttendantHeader />
    <div class="dashboard-layout">
      <AttendantSidebar />
      <div class="dashboard-content" :style="{ marginLeft: sidebarCollapsed ? '60px' : '250px' }">
        <div class="users-page">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-medium">Gerenciamento de Usuários</h2>
            <v-btn color="primary" @click="openNewUserDialog">
              <v-icon start>mdi-account-plus</v-icon>
              Novo Usuário
            </v-btn>
          </div>

          <!-- Filtros de Pesquisa -->
          <v-card class="mb-4">
            <v-card-text>
              <v-row>
                <v-col cols="12" sm="4">
                  <v-text-field v-model="search.name" label="Pesquisar por Nome" outlined dense
                    prepend-inner-icon="mdi-magnify" clearable @input="handleFilter"></v-text-field>
                </v-col>
                <v-col cols="12" sm="4">
                  <v-text-field v-model="search.email" label="Pesquisar por Email" outlined dense
                    prepend-inner-icon="mdi-email" clearable @input="handleFilter"></v-text-field>
                </v-col>
                <v-col cols="12" sm="4">
                  <v-select v-model="search.type" :items="userTypes" item-title="text" item-value="value"
                    label="Tipo de Usuário" outlined dense clearable @change="handleFilter"></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Tabela de Usuários -->
          <v-card>
            <v-data-table :headers="headers" :items="filteredUsers" :loading="loading" :items-per-page="10"
              class="elevation-1">
              <template v-slot:item.type="{ item }">
                <v-chip :color="item.type === 'Atendente' ? 'primary' : 'success'" small>
                  {{ item.type }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="primary" v-bind="attrs" v-on="on" @click="editUser(item)">
                      <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                  </template>
                  <span>Editar</span>
                </v-tooltip>
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small color="error" v-bind="attrs" v-on="on" @click="confirmDelete(item)">
                      <v-icon>mdi-delete</v-icon>
                    </v-btn>
                  </template>
                  <span>Excluir</span>
                </v-tooltip>
              </template>
            </v-data-table>
          </v-card>
        </div>
      </div>
    </div>

    <!-- Modal de criação/edição de usuário -->
    <v-dialog v-model="dialog.show" max-width="800px">
      <v-card>
        <v-card-title class="headline primary text-white">
          {{ dialog.isEdit ? 'Editar Usuário' : 'Novo Usuário' }}
        </v-card-title>
        <v-card-text class="pt-4">
          <v-form ref="form" @submit.prevent="saveUser">
            <v-row>
              <v-col cols="12">
                <v-tabs v-model="activeTab">
                  <v-tab>Dados Básicos</v-tab>
                  <v-tab :disabled="!isAttendant">Dados de Atendente</v-tab>
                </v-tabs>
              </v-col>
            </v-row>

            <v-tabs-items v-model="activeTab">
              <!-- Aba 1: Dados Básicos -->
              <v-tab-item>
                <v-row class="mt-4">
                  <v-col cols="12" md="6">
                    <v-text-field v-model="formData.name" label="Nome Completo*" required :rules="rules.name"
                      outlined></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="formData.email" label="Email*" type="email" required :rules="rules.email"
                      outlined></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="formData.password" label="Senha*" :type="showPassword ? 'text' : 'password'"
                      required :rules="rules.password" outlined :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                      @click:append="showPassword = !showPassword"></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="formData.userType" :items="userTypeOptions" item-title="text" item-value="value"
                      label="Tipo de Usuário*" required outlined @change="handleUserTypeChange"></v-select>
                  </v-col>
                </v-row>
              </v-tab-item>

              <!-- Aba 2: Dados de Atendente -->
              <v-tab-item>
                <v-row class="mt-4">
                  <v-col cols="12" md="6">
                    <v-select v-model="formData.sector_id" :items="sectors" item-title="name" item-value="id"
                      label="Setor*" :disabled="!isAttendant" :rules="isAttendant ? rules.required : []"
                      outlined></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select v-model="formData.function" :items="functionOptions" label="Função*"
                      :disabled="!isAttendant" :rules="isAttendant ? rules.required : []" outlined></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-alert v-if="isAttendant && formData.function === 'Admin'" type="warning" outlined>
                      Atenção: Usuários com função "Admin" terão acesso total ao sistema, incluindo esta tela de
                      gerenciamento de usuários.
                    </v-alert>
                  </v-col>
                </v-row>
              </v-tab-item>
            </v-tabs-items>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" text @click="closeDialog">Cancelar</v-btn>
          <v-btn color="primary" @click="saveUser" :loading="dialog.loading">
            {{ dialog.isEdit ? 'Atualizar' : 'Cadastrar' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog de confirmação para exclusão -->
    <v-dialog v-model="confirmDialog.show" max-width="400px">
      <v-card>
        <v-card-title class="headline error--text">Confirmar Exclusão</v-card-title>
        <v-card-text>
          Tem certeza que deseja excluir o usuário <strong>{{ confirmDialog.user?.name }}</strong>?
          <v-alert v-if="confirmDialog.user?.type === 'Atendente'" type="warning" dense class="mt-3">
            Atenção: Este usuário é um atendente. A exclusão pode afetar chamados vinculados a ele.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" text @click="confirmDialog.show = false">Cancelar</v-btn>
          <v-btn color="error" @click="deleteUser" :loading="confirmDialog.loading">Excluir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Alerta de feedback -->
    <v-snackbar v-model="feedback.show" :color="feedback.color" :timeout="4000">
      {{ feedback.message }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="feedback.show = false">Fechar</v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useSidebar } from '@/composables/useSidebar';
import AttendantHeader from '@/components/common/AttendantHeader.vue';
import AttendantSidebar from '@/components/common/AttendantSidebar.vue';
import api from '@/services/api';
import { attendantAuthService } from '@/services/attendant-auth.service';

const router = useRouter();
const { sidebarCollapsed } = useSidebar();
const loading = ref(false);
const users = ref([]);
const sectors = ref([]);
const activeTab = ref(0);
const showPassword = ref(false);

// Verifica se o usuário atual é um atendente admin
const checkAdminPermission = () => {
  const attendant = attendantAuthService.getAttendantData();
  if (!attendant || attendant.function !== 'Admin') {
    showFeedback('Acesso negado: apenas administradores podem acessar esta página', 'error');
    router.push('/attendant/dashboard');
  }
};

// Configurações de busca
const search = ref({
  name: '',
  email: '',
  type: null
});

// Opções para os selects
const userTypes = [
  { text: 'Usuário Comum', value: 'Usuário' },
  { text: 'Atendente', value: 'Atendente' }
];

const userTypeOptions = [
  { text: 'Usuário Comum', value: 'user' },
  { text: 'Atendente', value: 'attendant' }
];

const functionOptions = [
  'Admin',
  'Suporte N1',
  'Suporte N2',
  'Analista',
  'Coordenador'
];

// Cabeçalhos da tabela
const headers = [
  { text: 'Nome', value: 'name' },
  { text: 'Email', value: 'email' },
  { text: 'Tipo', value: 'type' },
  { text: 'Setor', value: 'sector.name' },
  { text: 'Função', value: 'function' },
  { text: 'Ações', value: 'actions', sortable: false }
];

// Estados do modal
const dialog = ref({
  show: false,
  isEdit: false,
  loading: false
});

// Estado para confirmação de exclusão
const confirmDialog = ref({
  show: false,
  user: null,
  loading: false
});

// Estado para feedback
const feedback = ref({
  show: false,
  message: '',
  color: 'success'
});

// Formulário
const form = ref(null);
const formData = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  userType: 'user',
  sector_id: null,
  function: 'Suporte N1'
});

// Regras de validação
const rules = {
  name: [v => !!v || 'Nome é obrigatório'],
  email: [
    v => !!v || 'Email é obrigatório',
    v => /.+@.+\..+/.test(v) || 'Email deve ser válido'
  ],
  password: [
    v => (dialog.value.isEdit && !v) || !!v || 'Senha é obrigatória',
    v => (dialog.value.isEdit && !v) || v.length >= 6 || 'Senha deve ter pelo menos 6 caracteres'
  ],
  required: [v => !!v || 'Campo obrigatório']
};

// Computed properties
const isAttendant = computed(() => formData.value.userType === 'attendant');

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchName = !search.value.name || user.name.toLowerCase().includes(search.value.name.toLowerCase());
    const matchEmail = !search.value.email || user.email.toLowerCase().includes(search.value.email.toLowerCase());

    // Mapeamento de tipos
    const typeMap = {
      'user': 'Usuário',
      'attendant': 'Atendente'
    };




    const matchType = !search.value.type ||
      user.type === typeMap[search.value.type] ||
      user.type === search.value.type;

    return matchName && matchEmail && matchType;
  });
});

// Métodos
const loadUsers = async () => {
  loading.value = true;
  try {
    // Primeiro carregamos usuários comuns
    const usersResponse = await api.get('/users');
    let usersList = [];

    if (usersResponse.data.success) {
      usersList = usersResponse.data.data.map(user => ({
        ...user,
        type: 'Usuário',
        sector: { name: 'N/A' },
        function: 'N/A'
      }));
    }

    // Depois carregamos atendentes
    const attendantsResponse = await api.get('/attendants');
    let attendantsList = [];

    if (attendantsResponse.data.success) {
      attendantsList = attendantsResponse.data.data.map(attendant => ({
        id: attendant.id,
        name: attendant.name,
        email: attendant.email,
        type: 'Atendente',
        sector: attendant.sector,
        function: attendant.function
      }));
    }

    // Combinamos os dois conjuntos de dados
    users.value = [...usersList, ...attendantsList];
  } catch (error) {
    console.error('Erro ao carregar usuários:', error);
    showFeedback('Erro ao carregar lista de usuários', 'error');
  } finally {
    loading.value = false;
  }
};

const loadSectors = async () => {
  try {
    const response = await api.get('/sectors');

    console.log('SECTOR======>> ', response.data.data);

    if (response.data.success) {
      sectors.value = response.data.data;
    }
  } catch (error) {
    console.error('Erro ao carregar setores:', error);
    showFeedback('Erro ao carregar setores', 'error');
  }
};

const handleUserTypeChange = () => {
  if (formData.value.userType === 'attendant') {
    // Se mudou para atendente, vamos para a segunda aba
    activeTab.value = 1;
  }
};

const handleFilter = () => {
  // Função para lidar com as mudanças nos filtros
  // O computed filteredUsers já cuida disso automaticamente
};

const openNewUserDialog = () => {
  dialog.value.isEdit = false;
  dialog.value.show = true;

  // Reset form data
  formData.value = {
    id: null,
    name: '',
    email: '',
    password: '',
    userType: 'user',
    sector_id: null,
    function: 'Suporte N1'
  };

  // Reset to first tab
  activeTab.value = 0;
};

const editUser = (user) => {
  dialog.value.isEdit = true;

  // Preencher formulário com dados do usuário
  formData.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '', // Não preencher senha na edição
    userType: user.type === 'Atendente' ? 'attendant' : 'user',
    sector_id: user.sector?.id || null,
    function: user.function !== 'N/A' ? user.function : 'Suporte N1'
  };

  dialog.value.show = true;

  // Definir aba ativa com base no tipo de usuário
  activeTab.value = user.type === 'Atendente' ? 1 : 0;
};

const closeDialog = () => {
  dialog.value.show = false;

  // Resetar formulário
  if (form.value) {
    form.value.reset();
  }
};

const saveUser = async () => {
  // Modificação na validação do formulário
  if (form.value) {
    const validation = await form.value.validate();
    if (!validation.valid) {
      return;
    }
  }

  dialog.value.loading = true;

  try {
    const isNewUser = !dialog.value.isEdit;
    const userData = {
      name: formData.value.name,
      email: formData.value.email,
      password: formData.value.password,
    };

    if (formData.value.userType === 'attendant') {
      // Dados específicos de atendente
      userData.is_attendant = true;
      userData.sector_id = formData.value.sector_id;
      userData.function = formData.value.function;
    }

    let response;

    if (isNewUser) {
      // Criação de novo usuário - adicionando logs para debug
      console.log('Criando novo usuário:', userData);

      if (formData.value.userType === 'attendant') {
        response = await api.post('/attendants', userData);
      } else {
        response = await api.post('/users', userData);
      }

      console.log('Resposta da API:', response);

      if (response.data.success) {
        showFeedback('Usuário criado com sucesso!', 'success');
        closeDialog();
        loadUsers(); // Recarregar lista de usuários
      }
    } else {
      const userId = formData.value.id;

      // Log para debug
      console.log('Atualizando usuário:', userId, userData);

      if (formData.value.userType === 'attendant') {
        response = await api.put(`/attendants/${userId}`, userData);
      } else {
        response = await api.put(`/users/${userId}`, userData);
      }
      console.log('Resposta da atualização:', response);

      if (response.data.success) {
        showFeedback('Usuário atualizado com sucesso!', 'success');
      }

    }
    closeDialog();
    await loadUsers(); // Recarregar lista de usuários

  } catch (error) {
    console.error('Erro ao salvar usuário:', error);
    console.error('Detalhes:', error.response?.data);
    showFeedback(
      error.response?.data?.message || 'Erro ao salvar usuário',
      'error'
    );
  } finally {
    dialog.value.loading = false;
  }
};

const confirmDelete = (user) => {
  confirmDialog.value.user = user;
  confirmDialog.value.show = true;
};

const deleteUser = async () => {
  if (!confirmDialog.value.user) return;

  confirmDialog.value.loading = true;

  try {
    const user = confirmDialog.value.user;
    const endpoint = user.type === 'Atendente'
      ? `/attendants/${user.id}`
      : `/users/${user.id}`;

    const response = await api.delete(endpoint);

    if (response.data.success) {
      showFeedback('Usuário excluído com sucesso!', 'success');
      loadUsers(); // Recarregar lista
    }
  } catch (error) {
    console.error('Erro ao excluir usuário:', error);
    showFeedback(
      error.response?.data?.message || 'Erro ao excluir usuário',
      'error'
    );
  } finally {
    confirmDialog.value.show = false;
    confirmDialog.value.loading = false;
  }
};

const showFeedback = (message, color = 'success') => {
  feedback.value = {
    show: true,
    message,
    color
  };
};

// Hook de ciclo de vida
onMounted(() => {
  checkAdminPermission();
  loadUsers();
  loadSectors();
});

// Assistir mudanças no tipo de usuário
watch(() => formData.value.userType, (newType) => {
  if (newType === 'user') {
    // Se mudar para usuário comum, resetar campos de atendente
    formData.value.sector_id = null;
    formData.value.function = 'Suporte N1';
  }
});
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f3f4f6;
}

.dashboard-layout {
  padding-top: 60px;
  min-height: calc(100vh - 60px);
}

.dashboard-content {
  transition: margin-left 0.3s ease;
  padding: 24px;
}

.users-page {
  padding: 24px;
  background-color: #f8f9fa;
  min-height: calc(100vh - 108px);
}

.v-data-table :deep(.v-data-table__wrapper) {
  overflow-x: auto;
}
</style>