// app.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <router-outlet></router-outlet>
  `,
  styles: []
})
export class AppComponent { 
  constructor(private titleService: Title) {}

  ngOnInit() {
    this.titleService.setTitle('PGMBH SUPP Atendimentos');
  }
}