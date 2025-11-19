<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const divisions = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedDivision = ref(null);

// Ambil data pengurus
onMounted(async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/pengurus");
    const data = res.data.data;

    // Kelompokkan pengurus berdasarkan divisi
    const grouped = {};
    data.forEach((item) => {
      if (!grouped[item.divisi]) grouped[item.divisi] = [];
      grouped[item.divisi].push(item);
    });

    // Buat array divisions, urutkan koordinator di index 0
    divisions.value = Object.entries(grouped).map(([divisi, members]) => {
      const koordinator = members.reduce((min, m) => (m.id < min.id ? m : min));

      const sortedMembers = [
        koordinator,
        ...members.filter((m) => m.id !== koordinator.id)
      ];

      return {
        divisi,
        members: sortedMembers,
        currentIndex: 0,   // default slide pertama = koordinator
        koordinator,       // simpan objek koordinator
      };
    });
  } catch (err) {
    error.value = err.message || "Gagal mengambil data";
  } finally {
    loading.value = false;
  }
});

// Navigasi carousel
const nextImage = (division) => {
  division.currentIndex = (division.currentIndex + 1) % division.members.length;
};
const prevImage = (division) => {
  division.currentIndex =
    (division.currentIndex - 1 + division.members.length) %
    division.members.length;
};

// Modal handling
const openDetail = (division) => {
  selectedDivision.value = { ...division, currentIndex: 0 };
};
const closeDetail = () => {
  selectedDivision.value = null;
};
const nextDetail = () => {
  const div = selectedDivision.value;
  div.currentIndex = (div.currentIndex + 1) % div.members.length;
};
const prevDetail = () => {
  const div = selectedDivision.value;
  div.currentIndex =
    (div.currentIndex - 1 + div.members.length) % div.members.length;
};
</script>

<template>
  <div class="profile-page">
    <h1 class="title">Struktur Pengurus KBMK UNMUL</h1>

    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else class="division-container">
      <div
        v-for="division in divisions"
        :key="division.divisi"
        class="division-card"
      >
        <div class="image-container">
          <img
            :src="`http://localhost:8000${division.members[division.currentIndex].foto_url}`"
            alt="Foto Pengurus"
            class="division-image"
          />
          <button
            v-if="division.members.length > 1"
            class="arrow left"
            @click.stop="prevImage(division)"
            aria-label="previous"
          >
            ‹
          </button>
          <button
            v-if="division.members.length > 1"
            class="arrow right"
            @click.stop="nextImage(division)"
            aria-label="next"
          >
            ›
          </button>
        </div>

        <div class="division-info">
          <h3>{{ division.divisi }}</h3>

          <!-- Tampilkan Koordinator hanya jika anggota > 1 -->
          <p v-if="division.members.length > 1" class="koor">
            Koordinator: <span>{{ division.koordinator.nama }}</span>
          </p>
          <!-- Jika hanya 1 anggota, tampilkan langsung namanya -->
          <p v-else class="koor">{{ division.members[0].nama }}</p>
        </div>

        <!-- Tombol detail -->
        <button class="detail-btn" @click="openDetail(division)">
          Detail
        </button>
      </div>
    </div>

    <!-- Modal Detail -->
    <div v-if="selectedDivision" class="modal-overlay" @click="closeDetail">
      <div class="modal-content" @click.stop>
        <button class="close-btn" @click="closeDetail">✕</button>
        <h2>{{ selectedDivision.divisi }}</h2>

        <div class="carousel">
          <img
            :src="`http://localhost:8000${selectedDivision.members[selectedDivision.currentIndex].foto_url}`"
            alt="Foto Pengurus"
            class="detail-image"
          />
          <button class="arrow left" @click="prevDetail">‹</button>
          <button class="arrow right" @click="nextDetail">›</button>
        </div>

        <div class="member-info">
          <h3>
            {{ selectedDivision.members[selectedDivision.currentIndex].nama }}
            <span
              v-if="selectedDivision.members.length > 1 && selectedDivision.members[selectedDivision.currentIndex].id === selectedDivision.koordinator.id"
              class="koor-label"
            >(Koordinator)</span>
          </h3>
          <p>
            {{ selectedDivision.members[selectedDivision.currentIndex].deskripsi }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* LAYOUT HALAMAN */
.profile-page {
  padding: 1.5rem;
  background: #f5f7fa;
}

/* JUDUL */
.title {
  color: #2b1560;
  text-align: center;
  margin-bottom: 1.25rem;
  font-size: 1.25rem;
  font-weight: 700;
}

/* GRID */
.division-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, 300px);
  justify-content: center;
  gap: 1rem;
  padding: 0 0.5rem;
}

/* CARD */
.division-card {
  position: relative;
  width: 300px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(12, 15, 30, 0.06);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding-bottom: 2.5rem;
}

/* GAMBAR */
.image-container {
  position: relative;
  height: 110px;
  overflow: hidden;
}
.division-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* PANAH */
.arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.85);
  border: none;
  font-size: 1rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.arrow.left {
  left: 8px;
}
.arrow.right {
  right: 8px;
}

/* INFO DIVISI */
.division-info {
  padding: 0.5rem 0.85rem 1rem;
  text-align: center;
}
.division-info h3 {
  margin: 0;
  color: #2b1560;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.1;
}
.koor {
  margin: 6px 0 0;
  color: #666;
  font-size: 0.82rem;
}

/* TOMBOL DETAIL */
.detail-btn {
  position: absolute;
  bottom: 0.75rem;
  right: 0.75rem;
  background: #3fa8e5;
  color: white;
  border: none;
  padding: 0.45rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.25s ease, transform 0.1s ease;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}
.detail-btn:hover {
  background: #2f94d1;
  transform: scale(1.05);
}

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  position: relative;
  width: 92%;
  max-width: 560px;
  text-align: center;
}
.close-btn {
  position: absolute;
  top: 10px;
  right: 12px;
  border: none;
  background: transparent;
  font-size: 1.2rem;
  cursor: pointer;
}
.detail-image {
  width: 100%;
  height: 300px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 0.85rem;
}
.member-info h3 {
  color: #2b1560;
  margin-bottom: 0.25rem;
}
.member-info p {
  color: #555;
  font-size: 0.95rem;
}

/* LABEL KOORDINATOR */
.koor-label {
  color: #2b1560;
  font-size: 0.85rem;
  margin-left: 6px;
  font-weight: 600;
}

/* RESPONSIVE */
@media (max-width: 360px) {
  .division-container {
    grid-template-columns: repeat(auto-fill, 260px);
  }
  .division-card {
    width: 260px;
  }
  .image-container {
    height: 100px;
  }
}
</style>
