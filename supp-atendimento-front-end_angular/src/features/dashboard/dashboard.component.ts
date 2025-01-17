// src/app/features/dashboard/dashboard.component.ts
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressBarModule } from '@angular/material/progress-bar';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatIconModule, MatButtonModule, MatProgressBarModule],
  template: `
    <div class="dashboard-container">
      <h1 class="mat-headline-5">Painel de Controle</h1>
      
      <div class="dashboard-cards">
        <mat-card>
          <mat-card-header>
            <mat-card-title>Chamados Abertos</mat-card-title>
            <mat-card-subtitle>Total de chamados ativos</mat-card-subtitle>
          </mat-card-header>
          <mat-card-content>
            <div class="metric">
              <mat-icon color="primary">assignment</mat-icon>
              <span class="number">10</span>
            </div>
            <mat-progress-bar mode="determinate" value="70"></mat-progress-bar>
          </mat-card-content>
        </mat-card>

        <mat-card>
          <mat-card-header>
            <mat-card-title>Em Andamento</mat-card-title>
            <mat-card-subtitle>Chamados sendo atendidos</mat-card-subtitle>
          </mat-card-header>
          <mat-card-content>
            <div class="metric">
              <mat-icon color="accent">trending_up</mat-icon>
              <span class="number">5</span>
            </div>
            <mat-progress-bar mode="determinate" value="50" color="accent"></mat-progress-bar>
          </mat-card-content>
        </mat-card>

        <mat-card>
          <mat-card-header>
            <mat-card-title>Concluídos</mat-card-title>
            <mat-card-subtitle>Últimos 30 dias</mat-card-subtitle>
          </mat-card-header>
          <mat-card-content>
            <div class="metric">
              <mat-icon color="primary">check_circle</mat-icon>
              <span class="number">25</span>
            </div>
            <mat-progress-bar mode="determinate" value="90" color="primary"></mat-progress-bar>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .dashboard-container {
      padding: 24px;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin-top: 24px;
    }

    .metric {
      display: flex;
      align-items: center;
      gap: 16px;
      margin: 16px 0;
    }

    .number {
      font-size: 32px;
      font-weight: 500;
    }

    mat-card {
      height: 100%;
    }

    mat-progress-bar {
      margin-top: 16px;
    }
  `]
})
export class DashboardComponent { }  // Make sure export is present