<template>
  <div id="admin-wrapper" :class="{ 'sidebar-collapsed': isCollapsed }">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-header">
        <img :src="logoOrganisasi" alt="Logo" class="sidebar-logo" />
        <h3 v-if="!isCollapsed">Panel Admin</h3>
      </div>

      <!-- SIDEBAR DENGAN MENU NAVIGASI BERDASARKAN ROLE -->
      <nav class="sidebar-menu">
        <!-- Dashboard - Semua Role -->
        <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminDashboard)" :class="{ active: activeComponent === SuperAdminDashboard }">
          <i class="fas fa-home"></i>
          <span v-if="!isCollapsed">Dashboard</span>
        </a>

        <!-- Menu untuk Super Admin -->
        <template v-if="userRole === 'superadmin'">
          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaProfil)" :class="{ active: activeComponent === SuperAdminKelolaProfil }">
            <i class="fas fa-id-card"></i>
            <span v-if="!isCollapsed">Kelola Profil KBMK</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaVisiMisi)" :class="{ active: activeComponent === SuperAdminKelolaVisiMisi }">
            <i class="fas fa-bullseye"></i>
            <span v-if="!isCollapsed">Kelola Visi & Misi</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaKalender)" :class="{ active: activeComponent === SuperAdminKelolaKalender }">
            <i class="fas fa-calendar-alt"></i>
            <span v-if="!isCollapsed">Kelola Kalender Kegiatan</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaAnggota)" :class="{ active: activeComponent === SuperAdminKelolaAnggota }">
            <i class="fas fa-user-friends"></i>
            <span v-if="!isCollapsed">Kelola Pengurus</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaGform)" :class="{ active: activeComponent === SuperAdminKelolaGform }">
            <i class="fas fa-link"></i>
            <span v-if="!isCollapsed">Kelola Link GForm</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaSurat)" :class="{ active: activeComponent === SuperAdminKelolaSurat }">
            <i class="fas fa-file-alt"></i>
            <span v-if="!isCollapsed">Kelola Surat</span>
          </a>
        </template>

        <!-- Menu untuk Admin (Sekretaris) -->
        <template v-if="userRole === 'admin'">
          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaGform)" :class="{ active: activeComponent === SuperAdminKelolaGform }">
            <i class="fas fa-link"></i>
            <span v-if="!isCollapsed">Kelola Link GForm</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaKegiatan)" :class="{ active: activeComponent === SuperAdminKelolaKegiatan }">
            <i class="fas fa-tasks"></i>
            <span v-if="!isCollapsed">Kelola Kegiatan KBMK</span>
          </a>

          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaSurat)" :class="{ active: activeComponent === SuperAdminKelolaSurat }">
            <i class="fas fa-file-alt"></i>
            <span v-if="!isCollapsed">Kelola Surat</span>
          </a>
        </template>

        <!-- Menu untuk Koordinator Divisi (ID 1 = Media) -->
        <template v-if="userRole === 'koor_divisi' && userDivisionId === 1">
          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaKegiatan)" :class="{ active: activeComponent === SuperAdminKelolaKegiatan }">
            <i class="fas fa-tasks"></i>
            <span v-if="!isCollapsed">Kelola Kegiatan KBMK</span>
          </a>
        </template>

        <!-- Menu untuk Koordinator Divisi lainnya (ID 2 = Perlengkapan, dst) -->
        <template v-if="userRole === 'koor_divisi' && userDivisionId !== 1">
          <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaSurat)" :class="{ active: activeComponent === SuperAdminKelolaSurat }">
            <i class="fas fa-file-alt"></i>
            <span v-if="!isCollapsed">Disposisi Surat</span>
          </a>
        </template>
      </nav>
    </aside>

    <!-- Wrapper konten utama -->
    <div class="admin-main-wrapper">
      <header class="admin-navbar">
        <div class="navbar-left">
          <button class="menu-toggle" @click="toggleSidebar">
            <i class="fas fa-bars"></i>
          </button>
        </div>

        <div class="navbar-right">
          <div class="dropdown" @click="toggleDropdown">
            <button class="dropdown-toggle">
              <i class="fas fa-user"></i> {{ userName }} ({{ userRole }}) <i class="fas fa-caret-down"></i>
            </button>
            
            <div class="dropdown-menu" v-if="dropdownOpen">
              <a href="#" @click.prevent="navigateTo(SuperAdminDashboard)">
                <i class="fas fa-tachometer-alt"></i> Dashboard Admin
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" @click.prevent="handleLogoutClick" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>
          </div>
        </div>
      </header>

      <section class="admin-content-area">
        <component :is="activeComponent" />
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, shallowRef, inject, onMounted } from 'vue'
import axios from 'axios'
import logoOrganisasi from '@/assets/logo.png'

// --- IMPORT SEMUA KOMPONEN HALAMAN ---
import SuperAdminDashboard from './SuperAdminDashboard.vue'
import SuperAdminKelolaProfil from './SuperAdminKelolaProfil.vue'
import SuperAdminKelolaVisiMisi from './SuperAdminKelolaVisi.vue'
import SuperAdminKelolaKalender from './SuperAdminKelolaKalender.vue'
import SuperAdminKelolaAnggota from './SuperAdminKelolaAnggota.vue'
import SuperAdminKelolaGform from './SuperAdminKelolaGform.vue'
import SuperAdminKelolaKegiatan from './SuperAdminKelolaKegiatan.vue'
import SuperAdminKelolaSurat from './SuperAdminKelolaSurat.vue'

// --- STATE & FUNGSI NAVIGASI ---
const isCollapsed = ref(false)
const dropdownOpen = ref(false)
const activeComponent = shallowRef(SuperAdminDashboard)

// Data User dari API
const userRole = ref('')
const userName = ref('Loading...')
const userDivisionId = ref(null)

const auth = inject('auth')

const toggleSidebar = () => (isCollapsed.value = !isCollapsed.value)
const toggleDropdown = () => (dropdownOpen.value = !dropdownOpen.value)

const navigateTo = (component) => {
  activeComponent.value = component
}

const handleLogoutClick = () => {
  if (confirm('Apakah Anda yakin ingin keluar?')) {
    auth.logout()
  }
}

// Fetch data user dari API /me
const fetchUserData = async () => {
  try {
    // Cek dengan key yang benar: auth_token
    const token = localStorage.getItem('auth_token')
    
    console.log('🔍 Checking token:', token ? 'Token found ✅' : 'Token NOT found ❌')
    
    if (!token) {
      console.error('❌ Token tidak ditemukan di localStorage')
      // Coba ambil dari user_data jika ada
      const storedUserData = localStorage.getItem('user_data')
      if (storedUserData) {
        try {
          const parsedData = JSON.parse(storedUserData)
          console.log('📦 Using cached user_data:', parsedData)
          userRole.value = parsedData.role || ''
          userName.value = parsedData.username || 'Admin'
          userDivisionId.value = parsedData.division_id || null
          return
        } catch (e) {
          console.error('Error parsing user_data:', e)
        }
      }
      userName.value = 'No Token'
      return
    }

    console.log('📡 Fetching user data from API...')
    
    const response = await axios.get('http://localhost:8000/api/me', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    console.log('✅ API Response:', response.data)

    const userData = response.data.data
    
    // Set data user
    userRole.value = userData.role || ''
    userName.value = userData.username || 'Admin'
    userDivisionId.value = userData.division_id || null

    console.log('===== USER DATA DEBUG =====')
    console.log('Full User Data:', userData)
    console.log('User Role:', userRole.value)
    console.log('User Name:', userName.value)
    console.log('User Division ID:', userDivisionId.value)
    console.log('===========================')
    
    // Simpan ke localStorage juga untuk cache
    localStorage.setItem('user_data', JSON.stringify(userData))
    
  } catch (error) {
    console.error('❌ Error fetching user data:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    userName.value = 'Error'
    userRole.value = 'error'
  }
}

// Fetch user data saat component dimount
onMounted(() => {
  fetchUserData()
})
</script>

<style>
/* --- Style untuk Dropdown --- */
.navbar-right {
  position: relative;
}

.dropdown-toggle {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 120%;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 200px;
  transform-origin: top right;
  animation: dropdownFadeIn 0.2s ease-out;
}

.dropdown-menu::before {
  content: '';
  position: absolute;
  top: -8px;
  right: 20px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 8px solid white;
}

@keyframes dropdownFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.dropdown-menu a {
  color: #333;
  text-decoration: none;
  padding: 0.8rem 1.2rem;
  transition: background 0.3s;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.dropdown-menu a:hover {
  background: #f8f9fa;
}

.dropdown-menu i {
  width: 16px;
  text-align: center;
  color: #6c757d;
}

.dropdown-menu a:hover i {
  color: #007bce;
}

.dropdown-divider {
  height: 1px;
  background-color: #e9ecef;
  margin: 0.25rem 0;
}

.logout-link {
  color: #dc3545;
}

.logout-link i {
  color: #dc3545;
}

.logout-link:hover {
  background-color: #f8d7da;
  color: #721c24;
}

.logout-link:hover i {
  color: #721c24;
}

/* --- Style Utama Layout --- */
html,
body {
  margin: 0;
  height: 100%;
  font-family: Avenir, Helvetica, Arial, sans-serif;
  background-color: #f0f2f5;
  color: #000;
  overflow: hidden;
}

#admin-wrapper {
  display: flex;
  height: 100vh;
  position: relative;
  background: #f0f2f5;
}

/* === SIDEBAR === */
.admin-sidebar {
  width: 260px;
  background: linear-gradient(180deg, #36a2eb 0%, #007bce 100%);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
  transition: width 0.3s ease;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  overflow-y: auto;
  z-index: 200;
}

.sidebar-collapsed .admin-sidebar {
  width: 90px;
}

.sidebar-header {
  text-align: center;
  margin: 1.5rem 0;
}

.sidebar-logo {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #fff;
  margin-bottom: 0.5rem;
  box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
}

.sidebar-header h3 {
  font-size: 1.1rem;
  font-weight: bold;
  color: #ffffff;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  padding: 0 1rem;
  gap: 0.3rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: #ffffffcc;
  text-decoration: none;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  transition: background 0.3s ease, transform 0.2s ease, color 0.3s;
  font-weight: 500;
}

.sidebar-link i {
  width: 22px;
  text-align: center;
  font-size: 1.1rem;
}

.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateX(4px);
  color: #fff;
}

.sidebar-link.active {
  background: rgba(255, 255, 255, 0.3);
  color: #fff;
  border-left: 4px solid #ffffff;
}

/* === KONTEN UTAMA === */
.admin-main-wrapper {
  flex: 1;
  margin-left: 260px;
  transition: margin-left 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.sidebar-collapsed .admin-main-wrapper {
  margin-left: 90px;
}

/* === NAVBAR FLOATING === */
.admin-navbar {
  position: fixed;
  top: 15px;
  left: 275px;
  right: 30px;
  height: 60px;
  background: linear-gradient(90deg, #007bce 0%, #0069b1 100%);
  color: white;
  border-radius: 12px;
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5rem;
  z-index: 1000;
  transition: all 0.3s ease;
}

.sidebar-collapsed .admin-navbar {
  left: 105px;
  right: 30px;
}

.navbar-left {
  display: flex;
  align-items: center;
}

.menu-toggle {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 1.8rem;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.menu-toggle:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.1);
}

/* === AREA KONTEN === */
.admin-content-area {
  flex: 1;
  padding: 90px 2rem 2rem 2rem;
  background-color: #f4f6f9;
  overflow-y: auto;
}
</style>