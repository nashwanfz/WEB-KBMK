<template>
  <div id="admin-wrapper" :class="{ 'sidebar-collapsed': isCollapsed }">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
      <div class="sidebar-header">
        <img :src="logoOrganisasi" alt="Logo" class="sidebar-logo" />
        <h3 v-if="!isCollapsed">Panel Admin</h3>
      </div>

      <!-- SIDEBAR DENGAN MENU NAVIGASI -->
      <nav class="sidebar-menu">
        <!-- 
          - @click.prevent="navigateTo(...)" : Memanggil fungsi navigasi saat diklik.
          - :class="{ active: ... }" : Menandai link yang sedang aktif.
        -->
        <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminDashboard)" :class="{ active: activeComponent === SuperAdminDashboard }">
          <i class="fas fa-home"></i>
          <span v-if="!isCollapsed">Dashboard</span>
        </a>

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

        <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaKegiatan)" :class="{ active: activeComponent === SuperAdminKelolaKegiatan }">
          <i class="fas fa-tasks"></i>
          <span v-if="!isCollapsed">Kelola Kegiatan KBMK</span>
        </a>
        <a href="#" class="sidebar-link" @click.prevent="navigateTo(SuperAdminKelolaSurat)" :class="{ active: activeComponent === SuperAdminKelolaSurat }">
          <i class="fas fa-file-alt"></i>
          <span v-if="!isCollapsed">Kelola Surat</span>
        </a>
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
              <i class="fas fa-user"></i> Admin <i class="fas fa-caret-down"></i>
            </button>
            <div class="dropdown-menu" v-if="dropdownOpen">
              <a href="#" @click.prevent="navigateTo(SuperAdminDashboard)">Dashboard Admin</a>
              <a href="#">Logout</a>
            </div>
          </div>
        </div>
      </header>

      <!-- 
        AREA KONTEN DINAMIS
        <slot> diganti dengan <component :is="activeComponent" />
        untuk merender komponen halaman yang dipilih.
      -->
      <section class="admin-content-area">
        <component :is="activeComponent" />
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, shallowRef } from 'vue'
import logoOrganisasi from '@/assets/logo.png'

// --- IMPORT SEMUA KOMPONEN HALAMAN ---
// PASTIKAN NAMA FILE YANG ANDA IMPORT SESUAI DENGAN NAMA FILE DI FOLDER ANDA
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

// shallowRef menyimpan definisi komponen yang sedang aktif.
// Defaultnya adalah SuperAdminDashboard.
const activeComponent = shallowRef(SuperAdminDashboard)

const toggleSidebar = () => (isCollapsed.value = !isCollapsed.value)
const toggleDropdown = () => (dropdownOpen.value = !dropdownOpen.value)

// Fungsi untuk mengganti komponen yang aktif saat link diklik
const navigateTo = (component) => {
  activeComponent.value = component
}
</script>

<style>
/* ... (biarkan style Anda tetap seperti ini) ... */
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
  left: 275px; /* 260px sidebar + 15px margin kiri */
  right: 30px; /* margin kanan tetap */
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

/* Saat sidebar di-collapse */
.sidebar-collapsed .admin-navbar {
  left: 105px; /* 90px sidebar + 15px margin kiri */
  right: 30px; /* tetap margin kanan */
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
  border-radius: 6px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.dropdown-menu a {
  color: #333;
  text-decoration: none;
  padding: 0.7rem 1.2rem;
  transition: background 0.3s;
}

.dropdown-menu a:hover {
  background: #f0f0f0;
}

/* === AREA KONTEN === */
.admin-content-area {
  flex: 1;
  padding: 90px 2rem 2rem 2rem;
  background-color: #f4f6f9;
  overflow-y: auto;
}
</style>