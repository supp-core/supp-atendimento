<template>
    <div class="parent-container">
        <v-card class="login-card">
            <v-card-text>
                <div class="login-header">
                    <div class="logo-text-container">
                        <img src="/assets/supp.png" alt="SUPP/PBH" class="logo">
                        <div class="text-wrapper">
                            <h1 class="supp-text">SUPP/PBH</h1>
                            <p class="environment">Portal do Atendente</p>
                        </div>
                    </div>
                </div>

                <v-form @submit.prevent="handleSubmit">
                    <div class="form-group">
                        <label>Email Corporativo</label>
                        <v-text-field v-model="email" type="email" required placeholder="Digite seu email"
                            variant="outlined" density="comfortable"></v-text-field>
                    </div>

                    <div class="form-group">
                        <label>Senha</label>
                        <v-text-field v-model="password" :type="showPassword ? 'text' : 'password'" required
                            placeholder="Digite sua senha" variant="outlined" density="comfortable"
                            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append-inner="showPassword = !showPassword"></v-text-field>
                    </div>

                    <v-btn type="submit" color="primary" block :loading="loading" size="large">
                        Entrar
                    </v-btn>
                </v-form>

                <v-alert v-if="error" type="error" class="mt-4">
                    {{ error }}
                </v-alert>

                <div v-if="error" class="error-message">
                    {{ error }}
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { attendantAuthService } from '@/services/attendant-auth.service'

const router = useRouter()
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const handleSubmit = async () => {
    loading.value = true
    error.value = ''

    try {
        await attendantAuthService.login(email.value, password.value)
        router.push('/attendant/dashboard')
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.parent-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f5f5f5;
}

.login-card {
    background: white;
    padding: 2.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    margin: 2rem auto;
}


.login-header {
    margin-bottom: 2rem;
}


.logo-text-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo-wrapper {
    flex-shrink: 0;
}

.logo {
    width: 80px;
    height: auto;
}

.text-wrapper {
    flex-grow: 1;
}

.supp-text {
    font-size: 1.5rem;
    color: #1a237e;
    margin: 0;
    font-weight: 500;
    margin: 0 0 0.25rem 0;
}

.environment {
    color: #666;
    font-weight: 500;
    margin: 0.5rem 0 0 0;
}

.form-group {
    margin-bottom: 1.5rem;
}


.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #1a237e;
}

.login-button {
    width: 100%;
    padding: 0.875rem;
    background-color: #1a237e;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.login-button:hover {
    background-color: #0d47a1;
}

.login-button:disabled {
    background-color: #9fa8da;
    cursor: not-allowed;
}

.error-message {
    color: #f44336;
    font-size: 0.875rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background-color: #ffebee;
    border-radius: 4px;
    text-align: center;
}

.parent-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem;
    background-color: #f5f5f5;
}
</style>