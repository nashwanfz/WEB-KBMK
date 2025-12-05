<template>
  <div class="welcome-page">
    <div class="welcome-header">
      <h2>Selamat datang, Admin!</h2>
      <p>Di sini Anda dapat mengelola konten website KBMK.</p>
    </div>

    <!-- Indikator Loading -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Memuat data dashboard...</p>
    </div>

    <!-- Pesan Error -->
    <div v-else-if="error" class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="fetchDashboardStats" class="retry-btn">Coba Lagi</button>
    </div>

    <!-- Tampilkan Kartu Informasi jika data berhasil dimuat -->
    <div v-else class="info-cards">
      <div class="info-card">
        <i class="fas fa-users"></i>
        <div class="card-info">
          <h3>Total Pengurus</h3>
          <p>{{ stats.totalPengurus }}</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-calendar-check"></i>
        <div class="card-info">
          <h3>Kegiatan Aktif</h3>
          <p>{{ stats.kegiatanAktif }}</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-file-alt"></i>
        <div class="card-info">
          <h3>Total Surat</h3>
          <p>{{ stats.totalSurat }}</p>
        </div>
      </div>
      <div class="info-card">
        <i class="fas fa-link"></i>
        <div class="card-info">
          <h3>Link GForm</h3>
          <p>{{ stats.totalGForm }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { dashboardService } from '../api/dashboardService';

// State untuk menyimpan data statistik
const stats = ref({
  totalPengurus: 0,
  kegiatanAktif: 0,
  totalSurat: 0,
  totalGForm: 0
});

// State untuk mengatur loading dan error
const isLoading = ref(true);
const error = ref(null);

// Fungsi untuk mengambil data statistik dari API
const fetchDashboardStats = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const data = await dashboardService.getStats();
    stats.value = data;
  } catch (err) {
    error.value = 'Gagal memuat data dashboard. Silakan coba lagi.';
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

// Ambil data saat komponen pertama kali dimuat
onMounted(() => {
  fetchDashboardStats();
});
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
  font-size: 2.5rem;
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

/* Style untuk Loading dan Error */
.loading-indicator, .error-message {
  text-align: center;
  padding: 3rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.loading-indicator i {
  font-size: 2rem;
  color: #007bce;
  margin-bottom: 1rem;
}

.error-message {
  color: #721c24;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
}

.error-message i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.retry-btn {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #007bce;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background-color: #0056b3;
}
</style>