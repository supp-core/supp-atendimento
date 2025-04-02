<template>
    <aside :class="['sidebar', { 'collapsed': isCollapsed }]">
        <button class="toggle-button" @click="toggleSidebar">
            <span class="toggle-icon">{{ isCollapsed ? '→' : '←' }}</span>
        </button>
        <nav>
            <ul>
                <li>
                    <router-link to="/attendant/dashboard" active-class="active">
                        <span class="icon">📊</span>
                        <span class="text" v-show="!isCollapsed">Dashboard</span>
                    </router-link>
                </li>
                <li>
                    <router-link to="/attendant/tickets" active-class="active">
                        <span class="icon">🔧</span>
                        <span class="text" v-show="!isCollapsed">Evoluir Atendimentos</span>
                    </router-link>
                </li>

                 <!-- Mostrar apenas se o atendente for Admin -->
                 <li v-if="isAdmin">
                    <router-link to="/attendant/admin/users" active-class="active">
                        <span class="icon">👥</span>
                        <span class="text" v-show="!isCollapsed">Gerenciar Usuários</span>
                    </router-link>
                </li>
            </ul>
        </nav>
    </aside>
</template>

<script setup>
import { useSidebar } from '@/composables/useSidebar';
import { ref,  computed, onMounted } from 'vue';
import { attendantAuthService } from '@/services/attendant-auth.service';
const attendantData = ref(null);
const { sidebarCollapsed, toggleSidebar } = useSidebar();
// Computed property para verificar se é admin
const isAdmin = computed(() => {
    return attendantData.value && attendantData.value.function === 'Admin';
});


const toggleSidebars = () => {
    isCollapsed.value = !isCollapsed.value;
};

const isCollapsed = sidebarCollapsed;
// Carregar dados do atendente ao montar o componente
onMounted(() => {
    attendantData.value = attendantAuthService.getAttendantData();
});



</script>

<style scoped>
.sidebar {
    position: fixed;
    left: 0;
    top: 60px;
    /* Altura do header */
    bottom: 0;
    width: 250px;
    background: white;
    padding: 1rem;
    box-shadow: 1px 0 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    z-index: 100;
}

.sidebar.collapsed {
    width: 60px;
}

.toggle-button {
    position: absolute;
    right: -12px;
    top: 20px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: white;
    border: 1px solid #e0e0e0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    z-index: 101;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li {
    margin-bottom: 0.5rem;
}

.sidebar a {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    color: #1a237e;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.sidebar a.active {
    background-color: #e8eaf6;
}

.icon {
    width: 24px;
    text-align: center;
    margin-right: 12px;
}

.text {
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease;
}

.collapsed .text {
    opacity: 0;
    width: 0;
    margin: 0;
}
</style>