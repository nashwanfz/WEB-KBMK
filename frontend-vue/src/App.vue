<template>
  <div>
    <!-- 
      App.vue sekarang mengatur semua tampilan utama.
      - 'public': Tampilkan PublicPage
      - 'login': Tampilkan Login
      - 'admin': Tampilkan AdminLayout
    -->
    <PublicPage v-if="currentView === 'public'" @show-login="currentView = 'login'" />
    <Login v-else-if="currentView === 'login'" @login-success="handleLoginSuccess" @show-home="currentView = 'public'" />
    <AdminLayout v-else-if="currentView === 'admin'" />
  </div>
</template>

<script setup>
import { ref, provide } from 'vue';
import PublicPage from './components/PublicPage.vue';
import Login from './components/Login.vue';
import AdminLayout from './components/AdminLayout.vue';

// Hanya SATU state untuk mengatur SEMUA tampilan
const currentView = ref('public'); // Bisa bernilai: 'public', 'login', 'admin'

// Fungsi yang dipanggil saat login berhasil (dari Login.vue)
const handleLoginSuccess = () => {
  currentView.value = 'admin';
};

// Fungsi untuk logout
const handleLogout = () => {
  currentView.value = 'public';
};

// Berikan fungsi handleLoginSuccess dan handleLogout ke komponen anak
provide('login-success', handleLoginSuccess);
provide('logout', handleLogout);
</script>

<!-- Style global, tanpa scoped -->
<style>
/* Style ini berlaku untuk SELURUH aplikasi */
html, body {
  margin: 0;
  padding: 0;
  font-family: Avenir, Helvetica, Arial, sans-serif;
}

/* Atur smooth scroll untuk seluruh aplikasi */
html {
  scroll-behavior: smooth;
}

html, body {
  overflow: auto !important; /* Gunakan !important untuk mengesampingkan aturan lain */
}

#app-wrapper {
  overflow: auto !important;
}
</style>