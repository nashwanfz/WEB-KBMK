tiddak usah pake router, ikuti saja ini:
<script setup>
  // Mengimpor 'ref' dari Vue untuk membuat variabel reaktif (state)
  import { ref } from 'vue';
  
  // Mengimpor semua komponen yang akan digunakan di halaman ini
  import Header from './components/Header.vue';
  import Footer from './components/Footer.vue';
  import Profile from './components/Profile.vue';
  import Members from './components/Members.vue';
  import Schedule from './components/Schedule.vue';
  import Activities from './components/Activities.vue';
  import Services from './components/Services.vue';
  import Login from './components/Login.vue';

  // Membuat "saklar" untuk mengontrol tampilan.
  // Nilai awalnya adalah 'home', yang berarti konten utama akan ditampilkan.
  const activePage = ref('home'); // Nilainya bisa 'home' atau 'login'
</script>

<template>
  <div id="app-wrapper">
    <!-- 
      Header akan selalu ditampilkan.
      @show-login="activePage = 'login'" adalah "pendengar".
      Saat Header mengirim sinyal 'showLogin', nilai activePage akan diubah menjadi 'login'.
    -->
    <Header @show-login="activePage = 'login'" />

    <!-- 
      v-if="activePage === 'home'" adalah sebuah kondisi.
      Konten utama ini HANYA akan ditampilkan jika nilai 'activePage' adalah 'home'.
    -->
    <main v-if="activePage === 'home'" class="main-content">
      <Profile id="profile-section" />
      <Members id="members-section" />
      <Schedule id="schedule-section" />
      <Activities id="activities-section" />
      <Services id="services-section" />      
    </main>

    <!-- 
      v-if="activePage === 'login'" adalah kondisi lainnya.
      Halaman Login ini HANYA akan ditampilkan jika nilai 'activePage' adalah 'login'.
      @show-home="activePage = 'home'" adalah pendengar agar bisa kembali ke beranda.
    -->
    <Login v-if="activePage === 'login'" @show-home="activePage = 'home'" />

    <!-- Footer akan selalu ditampilkan -->
    <Footer />
  </div>
</template>

<style>
/* CSS ini membuat efek scroll menjadi halus saat menu diklik */
html {
  scroll-behavior: smooth;
}

/* Style dasar untuk seluruh halaman agar konsisten */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  background-color: #f0f2f5;
}

#app-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh; 
}

.main-content {
  flex: 1; 
}

/* Style umum untuk setiap section agar memiliki tampilan yang seragam */
section {
  padding: 3rem 1.5rem;
  max-width: 1200px;
  margin: 0 auto 2rem auto;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

section:first-child {
  margin-top: 2rem;
}

/* Atur layout grid Pengurus dan Kegiatan menjadi 3 kolom */
.members-grid, .activities-grid {
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)) !important;
}
</style>

