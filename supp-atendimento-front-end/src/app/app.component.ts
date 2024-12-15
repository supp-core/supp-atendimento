import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

// Import all necessary Material modules
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  standalone: true,
  // Include all necessary modules in the imports array
  imports: [
    CommonModule,
    RouterModule,
    MatSidenavModule,     // For mat-sidenav-container, mat-sidenav, mat-sidenav-content
    MatToolbarModule,     // For mat-toolbar
    MatIconModule,        // For mat-icon
    MatListModule,        // For mat-nav-list, mat-list-item
    MatMenuModule,        // For mat-menu, mat-menu-item
    MatBadgeModule,       // For matBadge directive
    MatButtonModule       // For mat-button, mat-icon-button
  ]
})
export class AppComponent {
  userName = 'Usuário';
  userEmail = 'usuario@exemplo.com';
  notificationCount = 0;
  
  logout() {
    console.log('Logout clicked');
  }
}