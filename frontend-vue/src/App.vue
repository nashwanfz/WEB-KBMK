<script setup>
import { ref, computed, provide, onMounted } from 'vue';
// DIHAPUS: useRouter tidak lagi diperlukan karena kita tidak menggunakan router.push()
// import { useRouter } from 'vue-router'; 
import axios from 'axios';
import PublicPage from './components/PublicPage.vue';
import Login from './components/Login.vue';
import AdminLayout from './components/AdminLayout.vue';

// DIHAPUS: Inisialisasi router tidak lagi diperlukan
// const router = useRouter();

// --- STATE UNTUK MENGATUR TAMPILAN ---
const currentView = ref('public');

// --- STATE UNTUK AUTENTIKASI ---
const token = ref(localStorage.getItem('auth_token') || null);
const user = ref(null); // Akan diisi dengan data user lengkap (termasuk role)
const isLoading = ref(true);

// --- INSTANCE AXIOS DENGAN INTERCEPTOR ---
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  timeout: 5000 // Timeout 5 detik
});

// Request Interceptor: Tambahkan token ke setiap request
api.interceptors.request.use(config => {
  if (token.value) {
    config.headers.Authorization = `Bearer ${token.value}`;
  }
  console.log('Sending Request:', `${config.method.toUpperCase()} ${config.baseURL}${config.url}`);
  return config;
}, error => {
  return Promise.reject(error);
});

// Response Interceptor: Tangani error 401 (Unauthorized)
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      logout(); // Token tidak valid atau kadaluarsa, logout otomatis
    }
    return Promise.reject(error);
  }
);

// --- COMPUTED PROPERTIES UNTUK CEK ROLE (VERSI YANG DIPERBAIKI) ---
const hasRole = (role) => {
  if (!user.value || !user.value.roles) {
    return false;
  }
  // Jika roles adalah string, bandingkan langsung
  if (typeof user.value.roles === 'string') {
    return user.value.roles === role;
  }
  // Jika roles adalah array, cek apakah role ada di dalam array
  if (Array.isArray(user.value.roles)) {
    return user.value.roles.includes(role);
  }
  return false;
};

const isAdmin = computed(() => hasRole('admin') || hasRole('superadmin'));
const isSuperAdmin = computed(() => hasRole('superadmin'));
const isKoorMedia = computed(() => hasRole('koordinator_media'));

// --- FUNGSI AUTENTIKASI ---
const handleLoginSuccess = (loginData) => {
  const { token: receivedToken, user: receivedUser } = loginData;
    console.log('✅ App.vue: Event login-success DITERIMA!');
  console.log('Data yang diterima App.vue:', loginData);
  // Update state dengan token dan data user LENGKAP
  token.value = receivedToken;
  user.value = receivedUser;
  
  // Simpan ke localStorage agar tetap login setelah refresh
  localStorage.setItem('auth_token', receivedToken);
  localStorage.setItem('user_data', JSON.stringify(receivedUser));
  
  // Pindah ke view admin
  currentView.value = 'admin';
  // DIHAPUS: Tidak perlu router.push() karena perubahan currentView sudah cukup
  // router.push('/admin');
};

const logout = () => {
  token.value = null;
  user.value = null;
  
  // Hapus dari localStorage
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_data');
  localStorage.removeItem('rememberedUser');
  
  // Pindah ke view public
  currentView.value = 'public';
  // DIHAPUS: Tidak perlu router.push() karena perubahan currentView sudah cukup
  // router.push('/');
};

// --- FUNGSI VERIFIKASI TOKEN ---
const checkStoredToken = async () => {
  if (token.value) {
    console.log('Token ditemukan, memverifikasi ke server...');
    try {
      const response = await api.get('/me');
      user.value = response.data.data;
      currentView.value = 'admin';
      // DIHAPUS: Tidak perlu router.push() karena perubahan currentView sudah cukup
      // router.push('/admin');
      console.log('Token valid, login berhasil.');
    } catch (error) {
      console.error('Verifikasi token gagal:', error.message);
      logout(); // Token tidak valid, hapus dan arahkan ke login
    }
  }
  // PASTIKAN INI SELALU DIJALANKAN DI AKHIR FUNGSI
  isLoading.value = false;
};

// --- PROVIDE ---
provide('auth', {
  token,
  user,
  isAdmin,
  isSuperAdmin,
  isKoorMedia,
  logout,
  api
});

// Cek token saat aplikasi pertama kali dimuat
onMounted(() => {
  checkStoredToken();
});
</script>

<!-- Template dan Style tidak perlu diubah -->
<template>
  <div>
    <PublicPage v-if="currentView === 'public'" @show-login="currentView = 'login'" />
    <Login 
      v-else-if="currentView === 'login'" 
      @login-success="handleLoginSuccess" 
      @show-home="currentView = 'public'" 
    />
    <AdminLayout v-else-if="currentView === 'admin'" />
    
    <div v-if="isLoading" class="initial-loading">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat aplikasi...</p>
    </div>
  </div>
</template>

<style>
/* Style tidak berubah */
html, body {
  margin: 0;
  padding: 0;
  font-family: Avenir, Helvetica, Arial, sans-serif;
}
html {
  scroll-behavior: smooth;
}
html, body, #app {
  overflow: auto !important;
}
.initial-loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  font-size: 2rem;
  color: #007bce;
  z-index: 9999;
}
.initial-loading p {
  margin-top: 1rem;
  font-size: 1rem;
}
</style>