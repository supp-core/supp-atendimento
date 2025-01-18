<template>
  <v-navigation-drawer permanent>
    <!-- Perfil do usuário -->
    <v-list>
      <v-list-item
        prepend-avatar="https://cdn.vuetifyjs.com/images/john.png"
        :title="user?.name || 'Usuário'"
        :subtitle="user?.email || 'email@exemplo.com'"
      />
    </v-list>

    <v-divider></v-divider>

    <!-- Menu de navegação -->
    <v-list density="compact" nav>
      <!-- Dashboard -->
      <v-list-item
        prepend-icon="mdi-view-dashboard"
        title="Dashboard"
        value="dashboard"
        to="/"
      />

      <!-- Administração -->
      <v-list-group value="admin">
        <template v-slot:activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-cog"
            title="Administrador"
          />
        </template>

        <v-list-item
          prepend-icon="mdi-cog-outline"
          title="Configuração Módulos"
          value="config-modules"
          to="/config/modules"
        />

        <v-list-item
          prepend-icon="mdi-book-outline"
          title="Dispositivos Normativos"
          value="normative"
          to="/normative"
        />

        <v-list-item
          prepend-icon="mdi-domain"
          title="Domínios Administrativos"
          value="admin-domains"
          to="/admin/domains"
        />

        <v-list-item
          prepend-icon="mdi-account-multiple"
          title="Pessoas"
          value="people"
          to="/people"
        />
      </v-list-group>

      <!-- Chamados -->
      <v-list-group value="tickets">
        <template v-slot:activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-ticket"
            title="Chamados"
          />
        </template>

        <v-list-item
          prepend-icon="mdi-plus"
          title="Novo Chamado"
          value="new-ticket"
          to="/tickets/create"
        />

        <v-list-item
          prepend-icon="mdi-format-list-bulleted"
          title="Meus Chamados"
          value="my-tickets"
          to="/tickets"
        />
      </v-list-group>

      <!-- Logout -->
      <v-list-item
        prepend-icon="mdi-logout"
        title="Sair"
        value="logout"
        @click="handleLogout"
      />
    </v-list>
  </v-navigation-drawer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { authService } from '@/services/auth.service';

const router = useRouter();
const user = ref(null);

onMounted(() => {
  user.value = authService.getUser();
});

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};
</script>