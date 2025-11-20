<script setup>
  import logoOrganisasi from '@/assets/logo.png';

  // Emit event 'showLogin' saat tombol login diklik
  const emit = defineEmits(['showLogin']);
</script>

<template>
  <header class="main-header">
    <div class="container">
      <div class="logo-section">
        <img 
          :src="logoOrganisasi" 
          alt="Logo Organisasi" 
          class="organization-logo"
        >
        <span class="organization-name">KBMK UNMUL</span>
      </div>
      <nav>
        <a href="#">Beranda</a>
        <a href="#profile-section">Tentang Kami</a>
        <a href="#members-section">Pengurus</a>
        <a href="#schedule-section">Jadwal</a>
        <a href="#activities-section">Kegiatan</a>
        <a href="#services-section">Layanan</a>
        
        <!-- TAMBAHKAN TOMBOL LOGIN INI -->
        <button class="login-btn" @click="emit('showLogin')" title="Login Admin">
          <i class="fas fa-lock"></i>
        </button>
      </nav>
    </div>
  </header>
</template>

<style scoped>
/* Style Anda yang lama... */
.main-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
  background-color: #3fa8e5;
  color: white;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-right: +30px;
}
.logo-section {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  margin-left: -100px;
}
.organization-logo {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%; 
  margin-left: -100px;
}
.organization-name {
  font-size: 1.5rem;
  font-weight: bold;
}
nav {
  display: flex;
  align-items: center; /* Tambahkan ini untuk menyejajarkan tombol */
}
nav a {
  color: white;
  text-decoration: none;
  margin-left: 1.5rem;
  font-weight: 500;
  transition: color 0.3s;
}
nav a:hover {
  color: #08619c;
}

/* TAMBAHKAN STYLE UNTUK TOMBOL LOGIN */
.login-btn {
  background-color: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  margin-left: 1.5rem;
  transition: all 0.3s ease;
}
.login-btn:hover {
  background-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}
</style>