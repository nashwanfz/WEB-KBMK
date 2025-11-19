<template>
  <div class="layout">
    
    <!-- Sidebar -->
    <aside class="sidebar">
      <nav>
        <ul>
          <li>
            <a href="#" class="menu-item">Dashboard</a>
          </li>

          <!-- Menu khusus Super Admin -->
          <template v-if="role === 'super_admin'">
            <li><a href="#" class="menu-item">Kelola Profil KBMK</a></li>
            <li><a href="#" class="menu-item">Kelola Visi & Misi</a></li>
            <li><a href="#" class="menu-item">Kelola Kalender Kegiatan</a></li>
            <li><a href="#" class="menu-item">Kelola Pengurus</a></li>
            <li><a href="#" class="menu-item">Kelola Link GForm</a></li>
          </template>

          <!-- Menu untuk kedua role -->
          <li><a href="#" class="menu-item">Kelola Kegiatan KBMK</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Konten -->
    <main class="content">
      <slot />
    </main>

  </div>
</template>

<script setup>
defineProps({
  role: String
})
</script>

<style scoped>
.layout {
  display: flex;
}

.sidebar {
  width: 250px;
  background-color: #1a252f;
  min-height: 100vh;
  padding: 1.5rem 0;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-item {
  display: block;
  padding: 1rem 1.5rem;
  color: #bdc3c7;
  text-decoration: none;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.menu-item:hover {
  background-color: #2c3e50;
  color: white;
  border-left-color: #3498db;
}

.content {
  flex: 1;
  padding: 2rem;
  background: #f4f6f7;
}
</style>
