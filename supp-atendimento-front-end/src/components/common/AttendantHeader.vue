<template>
  <header class="app-header">
    <div class="header-brand">
      <SuppLogo :width="30" :height="30" />
      <span class="brand-name">SUPP</span>
      <span class="brand-divider"></span>
      <span class="brand-sub">Atendimento</span>
    </div>
    <div class="header-right">
      <div class="user-chip">
        <div class="avatar-circle">{{ userInitial }}</div>
        <div class="user-info">
          <span class="username">{{ nomeAtendente }}</span>
          <span class="user-meta" v-if="sector">{{ sector }}</span>
        </div>
      </div>
      <button @click="fazerLogout" class="logout-btn" title="Sair">
        <i class="mdi mdi-logout-variant"></i>
        <span>Sair</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { authService } from '@/services/auth.service'
import SuppLogo from '@/components/common/SuppLogo.vue'

const nomeAtendente = ref('')
const funcaoAtendente = ref('')
const sector = ref('')

const userInitial = computed(() =>
  nomeAtendente.value ? nomeAtendente.value.charAt(0).toUpperCase() : 'A'
)

const fazerLogout = async () => {
  try {
    await authService.logout()
  } finally {
    window.location.href = '/login'
  }
}

onMounted(() => {
  const attendant = authService.getAttendantData()
  if (attendant) {
    nomeAtendente.value = attendant.name
    funcaoAtendente.value = attendant.function
    sector.value = attendant.sector?.name || ''
  }
})
</script>

<style scoped>
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: #ffffff;
  border-bottom: 1px solid #e8eaf6;
  padding: 0 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a237e;
  letter-spacing: 0.5px;
}

.brand-divider {
  width: 1px;
  height: 18px;
  background: #c5cae9;
}

.brand-sub {
  font-size: 0.85rem;
  font-weight: 400;
  color: #5c6bc0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #1a237e;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.username {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.user-meta {
  font-size: 0.75rem;
  color: #6b7280;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: transparent;
  color: #5c6bc0;
  border: 1px solid #c5cae9;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: #e8eaf6;
  color: #1a237e;
  border-color: #9fa8da;
}

.logout-btn .mdi {
  font-size: 1rem;
}
</style>
