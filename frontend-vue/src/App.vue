<script setup>
import { ref, computed, provide, onMounted } from 'vue'

// IMPORT apiClient dan fungsi setAuthToken
import apiClient, { setAuthToken } from './api/axios.js'

import PublicPage from './components/PublicPage.vue'
import Login from './components/Login.vue'
import AdminLayout from './components/AdminLayout.vue'

// =====================
// STATE
// =====================
const currentView = ref('public')
const token = ref(localStorage.getItem('auth_token'))
const user = ref(null)
const isLoading = ref(true)

// =====================
// RESPONSE INTERCEPTOR
// =====================
apiClient.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      const url = err.config?.url || ''
      if (!url.includes('/me')) {
        console.warn('401 selain /me → logout')
        logout()
      } else {
        console.warn('401 dari /me → abaikan')
      }
    }
    return Promise.reject(err)
  }
)

// =====================
// ROLE HELPERS
// =====================
const hasRole = (role) => user.value?.role === role

const isAdmin = computed(() => hasRole('admin') || hasRole('superadmin'))
const isSuperAdmin = computed(() => hasRole('superadmin'))
const isKoorMedia = computed(() => hasRole('koor_divisi') && user.value?.division_id === 1)

// =====================
// AUTH ACTIONS
// =====================
const handleLoginSuccess = ({ token: t, user: u }) => {
  token.value = t
  user.value = u
  
  // DEBUG: Tambahkan ini
  console.log('Login successful. User data:', u);
  console.log('User role:', u.role);
  console.log('Is admin:', hasRole('admin') || hasRole('superadmin'));
  console.log('Is superadmin:', hasRole('superadmin'));

  localStorage.setItem('auth_token', t)
  localStorage.setItem('user_data', JSON.stringify(u))

  setAuthToken(t);

  currentView.value = 'admin'
}

const logout = () => {
  token.value = null
  user.value = null
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')

  setAuthToken(null);

  currentView.value = 'public'
}

// =====================
// CHECK TOKEN
// =====================
const checkStoredToken = async () => {
  const storedUserData = localStorage.getItem('user_data');
  if (token.value && storedUserData) {
    try {
      user.value = JSON.parse(storedUserData);
      
      // DEBUG: Tambahkan ini
      console.log('User data from localStorage:', user.value);
      console.log('User role:', user.value.role);
      console.log('Is admin:', hasRole('admin') || hasRole('superadmin'));
      console.log('Is superadmin:', hasRole('superadmin'));
      
      currentView.value = 'admin';
    } catch (e) {
      console.error("Gagal parsing user data dari localStorage", e);
      logout();
    }
  }

  if (token.value) {
    try {
      const res = await apiClient.get('/me')
      user.value = res.data.data
      
      // DEBUG: Tambahkan ini
      console.log('User data from API:', user.value);
      console.log('User role:', user.value.role);
      console.log('Is admin:', hasRole('admin') || hasRole('superadmin'));
      console.log('Is superadmin:', hasRole('superadmin'));
      
      currentView.value = 'admin'
      console.log('✅ Token valid')
    } catch {
      console.warn('❌ Token invalid')
      logout()
    }
  }

  isLoading.value = false
}

// =====================
// PROVIDE (SOLUSI UTAMA)
// =====================
// Buat satu objek auth reaktif yang berisi semua yang diperlukan
const auth = computed(() => ({
  token: token.value,
  user: user.value,
  api: apiClient,
  logout: logout,
  isAdmin: isAdmin.value,
  isSuperAdmin: isSuperAdmin.value,
  isKoorMedia: isKoorMedia.value
}));

// Sediakan objek auth ke seluruh aplikasi
provide('auth', auth);

onMounted(checkStoredToken)
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