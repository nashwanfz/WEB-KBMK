<template>
  <div class="activities-container">
    <h1>Kegiatan KBMK</h1>
    <p>Kegiatan KBMK Universitas Mulawarman tahun 2025.</p>

    <div class="activities-grid">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="activity-card"
      >
        <!-- FOTO DARI BACKEND -->
        <img
          :src="`${baseURL}${activity.foto_url}`"
          :alt="activity.nama"
          class="card-image"
        />
        <div class="card-content">
          <h3>{{ activity.nama }}</h3>
          <p class="card-meta">
            <span>📅 {{ formatDate(activity.tanggal) }}</span>
            <span>📍 {{ activity.lokasi }}</span>
          </p>
        </div>
        <button @click="showDetails(activity)" class="detail-button">
          Detail
        </button>
      </div>
    </div>

    <!-- MODAL DETAIL -->
    <div v-if="selectedActivity" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <button @click="closeModal" class="close-button">&times;</button>
        <img
          :src="`${baseURL}${selectedActivity.foto_url}`"
          :alt="selectedActivity.nama"
          class="modal-image"
        />
        <h2>{{ selectedActivity.nama }}</h2>
        <p class="modal-meta">
          <strong>Tanggal:</strong> {{ formatDate(selectedActivity.tanggal) }}
          <br />
          <strong>Lokasi:</strong> {{ selectedActivity.lokasi }}
        </p>
        <p class="modal-description">
          {{ selectedActivity.deskripsi }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const activities = ref([]);
const selectedActivity = ref(null);

// Ganti sesuai base URL backend kamu
const baseURL = "https://kbmk.unmul.ac.id/api";

// Fetch data dari backend Laravel
const fetchActivities = async () => {
  try {
    const response = await axios.get(`${baseURL}/api/documentations`);
    activities.value = response.data.data;
  } catch (error) {
    console.error("Gagal mengambil data kegiatan:", error);
  }
};

// Format tanggal ke bahasa Indonesia
const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const options = { day: "2-digit", month: "long", year: "numeric" };
  return new Date(dateStr).toLocaleDateString("id-ID", options);
};

const showDetails = (activity) => {
  selectedActivity.value = activity;
};

const closeModal = () => {
  selectedActivity.value = null;
};

// Panggil API saat komponen dimount
onMounted(fetchActivities);
</script>

<style scoped>
.activities-container {
  padding: 1rem;
}

h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.activities-container > p {
  text-align: center;
  color: #555;
  margin-bottom: 2.5rem;
}

.activities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.activity-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s, box-shadow 0.3s;
}

.activity-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.card-content {
  padding: 1rem;
  flex-grow: 1;
}

.card-content h3 {
  margin: 0 0 0.5rem;
  color: #2c3e50;
}

.card-meta {
  font-size: 0.9rem;
  color: #777;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-button {
  width: auto;
  align-self: flex-end;
  margin: 0 1rem 1rem 1rem;
  padding: 0.5rem 1.5rem;
  background-color: #3fa8e5;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.detail-button:hover {
  background-color: #08619c;
}

/* Style untuk Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: #fff;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  position: relative;
  max-height: 80vh;
  overflow-y: auto;
}

.close-button {
  position: absolute;
  top: 10px;
  right: 15px;
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #777;
}

.modal-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-meta {
  background-color: #f4f4f9;
  padding: 0.75rem;
  border-radius: 4px;
  margin: 1rem 0;
  color: #333;
  line-height: 1.5;
}

.modal-description {
  line-height: 1.6;
  color: #555;
}
</style>
