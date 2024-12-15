import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatTooltipModule } from '@angular/material/tooltip';

interface Ticket {
  id: number;
  title: string;
  status: string;
  priority: string;
  requester: string;
  created: Date;
}

@Component({
  selector: 'app-tickets',
  standalone: true,
  imports: [
    CommonModule, 
    MatTableModule, 
    MatButtonModule, 
    MatIconModule,
    MatChipsModule,
    MatTooltipModule
  ],
  template: `
    <div class="tickets-container">
      <header class="tickets-header">
        <h1 class="mat-headline-5">Chamados</h1>
        <button mat-raised-button color="primary">
          <mat-icon>add</mat-icon>
          Novo Chamado
        </button>
      </header>

      <table mat-table [dataSource]="tickets" class="tickets-table">
        <ng-container matColumnDef="id">
          <th mat-header-cell *matHeaderCellDef>ID</th>
          <td mat-cell *matCellDef="let ticket">#{{ticket.id}}</td>
        </ng-container>

        <ng-container matColumnDef="title">
          <th mat-header-cell *matHeaderCellDef>Título</th>
          <td mat-cell *matCellDef="let ticket">{{ticket.title}}</td>
        </ng-container>

        <ng-container matColumnDef="status">
          <th mat-header-cell *matHeaderCellDef>Status</th>
          <td mat-cell *matCellDef="let ticket">
            <mat-chip [color]="getStatusColor(ticket.status)" selected>
              {{ticket.status}}
            </mat-chip>
          </td>
        </ng-container>

        <ng-container matColumnDef="priority">
          <th mat-header-cell *matHeaderCellDef>Prioridade</th>
          <td mat-cell *matCellDef="let ticket">
            <mat-icon [color]="getPriorityColor(ticket.priority)" 
                     [matTooltip]="ticket.priority">
              {{getPriorityIcon(ticket.priority)}}
            </mat-icon>
          </td>
        </ng-container>

        <ng-container matColumnDef="requester">
          <th mat-header-cell *matHeaderCellDef>Solicitante</th>
          <td mat-cell *matCellDef="let ticket">{{ticket.requester}}</td>
        </ng-container>

        <ng-container matColumnDef="created">
          <th mat-header-cell *matHeaderCellDef>Data</th>
          <td mat-cell *matCellDef="let ticket">
            {{ticket.created | date:'dd/MM/yyyy HH:mm'}}
          </td>
        </ng-container>

        <ng-container matColumnDef="actions">
          <th mat-header-cell *matHeaderCellDef>Ações</th>
          <td mat-cell *matCellDef="let ticket">
            <button mat-icon-button color="primary" matTooltip="Visualizar">
              <mat-icon>visibility</mat-icon>
            </button>
            <button mat-icon-button color="accent" matTooltip="Editar">
              <mat-icon>edit</mat-icon>
            </button>
          </td>
        </ng-container>

        <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
        <tr mat-row *matRowDef="let row; columns: displayedColumns;"></tr>
      </table>
    </div>
  `,
  styles: [`
    .tickets-container {
      padding: 24px;
    }

    .tickets-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .tickets-table {
      width: 100%;
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .mat-mdc-row:hover {
      background: #f5f5f5;
    }

    .mat-mdc-header-cell {
      font-weight: 500;
      color: rgba(0,0,0,0.87);
    }

    .mat-mdc-cell {
      color: rgba(0,0,0,0.87);
    }
  `]
})
export class TicketsComponent {
  displayedColumns = ['id', 'title', 'status', 'priority', 'requester', 'created', 'actions'];
  
  tickets: Ticket[] = [
    { 
      id: 1, 
      title: 'Problema com impressora', 
      status: 'Aberto',
      priority: 'Alta',
      requester: 'João Silva',
      created: new Date('2024-03-14 09:30')
    },
    { 
      id: 2, 
      title: 'Acesso ao sistema', 
      status: 'Em andamento',
      priority: 'Média',
      requester: 'Maria Santos',
      created: new Date('2024-03-14 10:15')
    },
  ];

  getStatusColor(status: string): string {
    const statusMap: { [key: string]: string } = {
      'Aberto': 'warn',
      'Em andamento': 'accent',
      'Concluído': 'primary'
    };
    return statusMap[status] || 'primary';
  }

  getPriorityIcon(priority: string): string {
    const priorityMap: { [key: string]: string } = {
      'Alta': 'priority_high',
      'Média': 'remove',
      'Baixa': 'arrow_downward'
    };
    return priorityMap[priority] || 'help';
  }

  getPriorityColor(priority: string): string {
    const priorityMap: { [key: string]: string } = {
      'Alta': 'warn',
      'Média': 'accent',
      'Baixa': 'primary'
    };
    return priorityMap[priority] || 'primary';
  }
}