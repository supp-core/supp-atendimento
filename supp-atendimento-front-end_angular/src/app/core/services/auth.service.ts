// src/app/core/services/auth.service.ts

import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, tap, catchError } from 'rxjs';
import { Router } from '@angular/router';

// Interfaces para melhor tipagem
export interface User {
  id: number;
  email: string;
  name: string;
  roles: string[];
}

export interface LoginResponse {
  success: boolean;
  data?: {
    user: User;
    token: string;
  };
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // Configurações básicas do serviço
  private readonly API_URL = 'http://localhost:8000/api';
  private readonly TOKEN_KEY = 'auth_token';
  private readonly USER_KEY = 'user_data';

  // Observable para controle do estado de autenticação
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(this.hasValidToken());
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    // Verifica o token ao inicializar o serviço
    this.checkInitialAuthentication();
  }

  login(email: string, password: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(
        `${this.API_URL}/login`,
        { email, password }
    ).pipe(
        tap(response => {
            if (response.success && response.data) {
                this.storeAuthData(response.data);
                this.isAuthenticatedSubject.next(true);
            }
        }),
        catchError(error => {
            this.isAuthenticatedSubject.next(false);
            this.clearAuthData();
            throw error;
        })
    );
}

  // Método de logout melhorado
  logout(): void {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.getToken()}`);

    this.http.post(
      `${this.API_URL}/logout`,
      {},
      { headers }
    ).subscribe({
      next: () => this.handleLogout(),
      error: () => this.handleLogout(),
      complete: () => console.log('Logout completado')
    });
  }

  // Métodos auxiliares privados
  private checkInitialAuthentication(): void {
    const token = this.getToken();
    const userData = this.getUserData();
    
    if (token && userData) {
      this.isAuthenticatedSubject.next(true);
    } else {
      this.clearAuthData();
    }
  }

  private storeAuthData(data: { token: string; user: User }): void {
    localStorage.setItem(this.TOKEN_KEY, data.token);
    localStorage.setItem(this.USER_KEY, JSON.stringify(data.user));
  }

  private clearAuthData(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    this.isAuthenticatedSubject.next(false);
  }

  private handleLogout(): void {
    this.clearAuthData();
    this.router.navigate(['/login']);
  }

  // Métodos públicos de utilidade
  public getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  public getUserData(): User | null {
    const userData = localStorage.getItem(this.USER_KEY);
    try {
      return userData ? JSON.parse(userData) : null;
    } catch {
      console.error('Erro ao parsear dados do usuário');
      return null;
    }
  }

  public hasValidToken(): boolean {
    const token = this.getToken();
    return !!token;
  }

  // Método para verificar se o token ainda é válido
  public isTokenValid(): boolean {
    const token = this.getToken();
    if (!token) return false;

    // Aqui você pode adicionar lógica adicional para verificar
    // a validade do token, como verificar a data de expiração
    return true;
  }
}