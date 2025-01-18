<template>
    <v-card class="mx-auto" max-width="400">
      <v-card-title>Login</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field
            v-model="email"
            label="Email"
            type="email"
            required
          ></v-text-field>
          
          <v-text-field
            v-model="password"
            label="Senha"
            type="password"
            required
          ></v-text-field>
  
          <v-btn
            color="primary"
            type="submit"
            block
            :loading="loading"
          >
            Entrar
          </v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { authService } from '@/services/auth.service';
  
  const email = ref('');
  const password = ref('');
  const loading = ref(false);
  const router = useRouter();
  
  const handleSubmit = async () => {
    try {
      loading.value = true;
      await authService.login(email.value, password.value);
      router.push('/tickets');
    } catch (error) {
      console.error('Erro ao fazer login:', error);
      // Aqui você pode adicionar uma notificação de erro
    } finally {
      loading.value = false;
    }
  };
  </script>