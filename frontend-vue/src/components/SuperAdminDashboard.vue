<template>
  <div class="welcome-page">
    <div class="welcome-header">
      <h2>Selamat datang, Super Admin!</h2>
      <p>Di sini Anda dapat mengelola semua konten website KBMK.</p>
    </div>

    <!-- Opsi: Tambahkan beberapa kartu informasi singkat untuk mempercantik dashboard -->
    <div class="info-cards">
      <div class="info-card">
        <i class="fas fa-users"></i>
        <div class="card-info">
          <h3>Total Pengurus</h3>
          <p>25</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-calendar-check"></i>
        <div class="card-info">
          <h3>Kegiatan Aktif</h3>
          <p>8</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-file-alt"></i>
        <div class="card-info">
          <h3>Total Surat</h3>
          <p>12</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-link"></i>
        <div class="card-info">
          <h3>Link GForm</h3>
          <p>5</p>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- Tidak perlu import apapun karena ini hanya halaman statis -->
<script setup>
// Logika khusus untuk dashboard bisa ditambahkan di sini di masa depan
</script>

<style scoped>
.welcome-page {
  padding: 1.5rem;
}

.welcome-header h2 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 2rem;
}

.welcome-header p {
  color: #7f8c8d;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

/* Style untuk kartu informasi tambahan */
.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.info-card {
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.info-card i {
  font-size: 2rem;
  color: #007bce;
  background-color: #e9f5ff;
  padding: 1rem;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-info h3 {
  margin: 0;
  color: #495057;
  font-size: 0.9rem;
  font-weight: 600;
}

.card-info p {
  margin: 0;
  color: #2c3e50;
  font-size: 1.8rem;
  font-weight: bold;
}
</style>