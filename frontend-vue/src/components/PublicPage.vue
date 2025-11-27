<script setup>
  import Header from './Header.vue';
  import Footer from './Footer.vue';
  import Profile from './Profile.vue';
  import Members from './Members.vue';
  import Schedule from './Schedule.vue';
  import Activities from './Activities.vue';
  import Services from './Services.vue';

  // Hanya perlu emit untuk memberi tahu App.vue
  const emit = defineEmits(['showLogin']);

  const handleShowLogin = () => {
    // Cukup beri tahu App.vue untuk mengubah tampilan
    emit('showLogin');
  };
</script>

<template>
  <div id="app-wrapper">
    <Header @show-login="handleShowLogin" />

    <!-- Konten utama selalu ditampilkan, tidak ada v-if lagi -->
    <main class="main-content">
      <Profile id="profile-section" />
      <Members id="members-section" />
      <Schedule id="schedule-section" />
      <Activities id="activities-section" />
      <Services id="services-section" />      
    </main>

    <Footer />
  </div>
</template>
<style scoped>
#app-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh; 
}

.main-content {
  flex: 1; 
  /* INI ADALAH BARIS YANG SANGAT PENTING */
  overflow-y: auto; 
}

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

.members-grid, .activities-grid {
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)) !important;
}
</style>