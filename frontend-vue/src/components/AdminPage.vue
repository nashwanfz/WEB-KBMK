<template>

  <AdminLayout role="admin">
    <div class="welcome-page">
      <h2>Selamat datang, Admin!</h2>
      <p>Anda dapat mengelola data kegiatan KBMK di sini.</p>
      
      <div class="cards-grid">
        <div class="card">
          <h3>Kegiatan KBMK</h3>
          <p>Kelola data kegiatan.</p>
          <button class="manage-btn">Kelola</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../components/AdminLayout.vue'
</script>

<style scoped>
.welcome-page h2 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}
.welcome-page p {
  color: #7f8c8d;
  margin-bottom: 2rem;
}
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}
.card {
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  border-left: 5px solid #2ecc71; 
}
.card h3 {
  margin-top: 0;
  color: #2c3e50;
}
.card p {
  color: #7f8c8d;
  font-size: 0.9rem;
}
.manage-btn {
  display: inline-block;
  padding: 8px 20px;
  margin-top: 1rem;
  border: none;
  border-radius: 5px;
  background-color: #3498db;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.manage-btn:hover {
  background-color: #2980b9;
}
</style>