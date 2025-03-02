import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import TicketsView from '../views/TicketsView.vue';
import AttendantLoginView from '../views/AttendantLoginView.vue'; // Adicione esta linha
//import CreateTicketForUserView from '../views/CreateTicketForUserView.vue';

const routes = [
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/tickets',
    name: 'tickets',
    component: TicketsView,
    meta: { requiresAuth: true }
  },
  {
    path: '/tickets/create',
    name: 'create-ticket',
    component: () => import('@/views/CreateTicket.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/attendant/login',
    name: 'attendant-login',
    component: () => import('@/views/AttendantLoginView.vue')
  },
  {
    path: '/attendant/dashboard',
    name: 'attendant-dashboard',
    component: () => import('../views/AttendantDashboard.vue'),
    meta: { requiresAttendantAuth: true }
  },
  {
    path: '/attendant/tickets',
    name: 'attendant-tickets',
    component: () => import('@/views/AttendantTicketsView.vue'),
    meta: { requiresAttendantAuth: true }
  },
  {
    path: '/tickets/:id',
    name: 'ticket-details',
    component: () => import('@/views/TicketsView.vue'),
    props: true
  },
  {
    path: '/attendant/tickets/create-for-user',
    name: 'create-ticket-for-user',
    component: () => import('@/views/AttendantTicketsView.vue'),
    meta: { requiresAttendantAuth: true, requiresAdmin: true }
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

// router/index.js

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAttendantAuth)) {
      // Verifica autenticação de atendente
      const attendantToken = localStorage.getItem('attendant_token')
      if (!attendantToken) {
          next('/attendant/login')
      } else {
          next()
      }
  } else if (to.matched.some(record => record.meta.requiresAuth)) {
      // Verifica autenticação de usuário comum
      const userToken = localStorage.getItem('token');
      const attendantData = localStorage.getItem('attendant');
      if (!token || !attendantData) {
        next('/attendant/login');
      } else if (to.matched.some(record => record.meta.requiresAdmin)) {
        // Verificação adicional para rotas que exigem perfil de admin
        const attendant = JSON.parse(attendantData);
        if (attendant.function !== 'Admin') {
          next('/attendant/dashboard'); // Redireciona se não for admin
        } else {
          next();
        }
      } else {
        next();
      }
    } else {
      next();
    }
})

// Aqui está a exportação que estava faltando
export default router;

