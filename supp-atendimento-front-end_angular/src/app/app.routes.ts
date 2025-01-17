// app.routes.ts
import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  {
    path: 'dashboard',
    loadComponent: () => import('../features/dashboard/dashboard.component')
          .then(m => m.DashboardComponent),
    canActivate: [AuthGuard]
  },
  {
    path: 'tickets',
    loadComponent: () => import('../features/tickets/tickets.component')
          .then(m => m.TicketsComponent),
    canActivate: [AuthGuard]
  },
  {
    path: '',
    redirectTo: 'dashboard',
    pathMatch: 'full'
  },
  {
    path: 'login',
    loadComponent: () => import('./core/auth/login/login.component')
      .then(m => m.LoginComponent)
  },
];