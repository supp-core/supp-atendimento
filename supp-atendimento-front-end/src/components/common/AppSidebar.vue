<template>
  <aside class="sidebar">
    <nav>
      <ul>
        <li>
          <router-link to="/dashboard" active-class="active">Dashboard</router-link>
        </li>
        <li>
          <router-link to="/tickets" active-class="active">Chamados</router-link>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 250px;
  background: white;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border-radius: 8px;
}

.sidebar ul {
  list-style: none;
  padding: 0;
}

.sidebar li {
  margin-bottom: 0.5rem;
}

.sidebar a {
  display: block;
  padding: 0.5rem 1rem;
  color: #1a237e;
  text-decoration: none;
  border-radius: 4px;
}

.sidebar a.active {
  background-color: #e8eaf6;
}
</style>