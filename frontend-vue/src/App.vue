<template>
  <div>
    <!-- 
      App.vue mengatur semua tampilan utama.
      - 'public': Tampilkan PublicPage
      - 'login': Tampilkan Login
      - 'admin': Tampilkan AdminLayout (yang berisi KelolaKalender)
    -->
    <PublicPage v-if="currentView === 'public'" @show-login="currentView = 'login'" />
    <Login 
      v-else-if="currentView === 'login'" 
      @login-success="handleLoginSuccess" 
      @show-home="currentView = 'public'" 
    />
    <AdminLayout v-else-if="currentView === 'admin'" />
    
    <!-- Loading screen saat mengecek token saat pertama kali load -->
    <div v-if="isLoading" class="initial-loading">
      <i class="fas fa-spinner fa-spin"></i>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, provide, onMounted } from 'vue';
import PublicPage from './components/PublicPage.vue';
import Login from './components/Login.vue';
import AdminLayout from './components/AdminLayout.vue';

// --- STATE UNTUK MENGATUR TAMPILAN ---
const currentView = ref('public'); // Bisa bernilai: 'public', 'login', 'admin'

// --- STATE UNTUK AUTENTIKASI ---
const token = ref(localStorage.getItem('token') || null);
const user = ref(token.value ? JSON.parse(localStorage.getItem('user') || 'null') : null);
const isLoading = ref(true);

// Computed property untuk status login
const isLoggedIn = computed(() => !!token.value);

// --- FUNGSI AUTENTIKASI ---

// Dipanggil saat login berhasil (dari Login.vue)
const handleLoginSuccess = (loginData) => {
  const { token: receivedToken, user: receivedUser } = loginData;
  
  // Update state
  token.value = receivedToken;
  user.value = receivedUser;
  
  // Simpan ke localStorage agar tetap login setelah refresh
  localStorage.setItem('token', receivedToken);
  localStorage.setItem('user', JSON.stringify(receivedUser));
  
  // Pindah ke view admin
  currentView.value = 'admin';
};

// Dipanggil saat tombol logout ditekan (dari AdminLayout.vue)
const handleLogout = () => {
  // Hapus state
  token.value = null;
  user.value = null;
  
  // Hapus dari localStorage
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  
  // Pindah ke view public
  currentView.value = 'public';
};

// Cek status login saat aplikasi pertama kali dimuat
onMounted(() => {
  // HAPUS atau KOMENTARI blok kode ini
  // if (token.value) {
  //   currentView.value = 'admin';
  // }
  
  // Sembunyikan loading screen
  isLoading.value = false;
});

// --- PROVIDE ---
// Sediakan data dan fungsi autentikasi ke semua komponen anak
provide('auth', {
  token,
  user,
  isLoggedIn,
  handleLogout
});
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

html, body, #app {
  overflow: auto !important; /* Gunakan !important untuk mengesampingkan aturan lain */
}

/* Loading screen awal */
.initial-loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 2rem;
  color: #007bce;
  z-index: 9999;
}
</style>