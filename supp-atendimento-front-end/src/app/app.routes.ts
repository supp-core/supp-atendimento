import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'dashboard',
    // Use explicit file extension
    loadComponent: () => import('../features/dashboard/dashboard.component')
          .then(m => m.DashboardComponent)
  },
  {
    path: 'tickets',
    loadComponent: () => import('../features/tickets/tickets.component')
          .then(m => m.TicketsComponent)
  },
  {
    path: '',
    redirectTo: 'dashboard',
    pathMatch: 'full'
  }
];