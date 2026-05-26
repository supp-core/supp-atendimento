import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import TicketsView from '../views/TicketsView.vue';
import { authService } from '@/services/auth.service';

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
    path: '/attendant/login',
    redirect: '/login'
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
    path: '/attendant/admin/users',
    name: 'admin-users',
    component: () => import('@/views/AdminUsersView.vue'),
    meta: {
      requiresAttendantAuth: true,
      requiresAdmin: true
    }
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

router.beforeEach((to, _from, next) => {
  const token = authService.getToken();

  if (to.matched.some(record => record.meta.requiresAttendantAuth)) {
    if (!token || !authService.isAttendant()) {
      return next('/login');
    }
    if (to.matched.some(record => record.meta.requiresAdmin) && !authService.isAdmin()) {
      return next('/attendant/dashboard');
    }
    return next();
  }

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      return next('/login');
    }
    return next();
  }

  next();
});

export default router;
