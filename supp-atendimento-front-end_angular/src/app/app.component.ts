// app.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatListModule } from '@angular/material/list';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatButtonModule } from '@angular/material/button';
import { Title } from '@angular/platform-browser';
import { AuthService } from './core/services/auth.service';
import { MatBadgeModule } from '@angular/material/badge'; // Adicionamos esta importação

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    CommonModule, 
    RouterModule,
    MatSidenavModule,
    MatToolbarModule,
    MatListModule,
    MatIconModule,
    MatMenuModule,
    MatButtonModule,
    MatBadgeModule  // Adicionamos o módulo aos imports
  ],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  userName: string = '';
  userEmail: string = '';
  notificationCount: number = 0;

  constructor(
    private titleService: Title,
    private authService: AuthService
  ) {}

  ngOnInit() {
    this.titleService.setTitle('SUPP Atendimentos');
    const userData = this.authService.getUserData();
    if (userData) {
      this.userName = userData.name;
      this.userEmail = userData.email;
    }
  }

  logout() {
    this.authService.logout();
  }
}