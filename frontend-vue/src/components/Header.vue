<script setup>
  import logoOrganisasi from '@/assets/logo.png';

  // PERUBAHAN 1: Mendefinisikan "sinyal" (event) bernama 'showLogin'
  // Ini memberitahu Vue bahwa komponen ini bisa mengirim sinyal keluar.
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
      </nav>
    </div>
  </header>
</template>

<style scoped>
/* Tidak ada perubahan pada CSS di file ini */
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
nav a {
  color: white;
  text-decoration: none;
  margin-left: 1.5rem;
  font-weight: 500;
  transition: color 0.3s;
  margin-right:
}
nav a:hover {
  color: #08619c;
}
</style>

