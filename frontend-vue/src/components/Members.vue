<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const divisions = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedDivision = ref(null);

// Fungsi untuk mendapatkan URL foto yang benar
const getPhotoUrl = (foto) => {
  if (!foto) return 'https://i.pravatar.cc/300?d=mp';
  if (foto.startsWith('http') || foto.startsWith('data:')) return foto;
  return `https://kbmk.unmul.ac.id/api/storage/${foto}`;
};

// Fungsi untuk menentukan prioritas jabatan (untuk mengurutkan anggota dalam divisi)
const getPositionPriority = (jabatan) => {
  if (!jabatan) return 999;
  const j = jabatan.toLowerCase();
  
  // Koordinator divisi (prioritas tertinggi di dalam divisi)
  if (j.includes('koordinator') || j.includes('koor')) return 10;
  if (j.includes('wakil')) return 11;
  
  // Staff/anggota
  if (j.includes('staff') || j.includes('anggota')) return 20;
  
  return 15; // Default untuk jabatan lain
};

// Fungsi untuk menentukan apakah seseorang adalah koordinator
const isKoordinator = (member) => {
  if (!member.jabatan) return false;
  const j = member.jabatan.toLowerCase();
  return j.includes('koordinator') || j.includes('koor');
};

// Fungsi untuk menentukan prioritas divisi (untuk mengurutkan kartu di tampilan utama)
const getDivisionPriority = (divisionName) => {
  const name = divisionName.toLowerCase();
  if (name.includes('ketua')) return 1;
  if (name.includes('sekretaris')) return 2;
  if (name.includes('bendahara')) return 3;
  return 10; // Prioritas untuk divisi lain
};

// Ambil data pengurus
onMounted(async () => {
  try {
    const res = await axios.get("https://kbmk.unmul.ac.id/api/api/pengurus");
    const data = res.data.data;

    // Kelompokkan pengurus berdasarkan division.nama
    const grouped = {};
    data.forEach((item) => {
      const division = item.division;
      if (!division) return; // Lewati jika tidak ada data divisi

      const divisionName = division.nama;
      
      if (!grouped[divisionName]) {
        // Inisialisasi dengan data divisi (nama dan deskripsi) dan array anggota kosong
        grouped[divisionName] = {
          divisi: divisionName,
          deskripsi: division.deskripsi, // SIMPAN DESKRIPSI DIVISI
          members: []
        };
      }
      
      // Tambahkan anggota ke divisi yang sesuai
      grouped[divisionName].members.push(item);
    });

    // Buat array divisions dari object yang sudah dikelompokkan
    const divisionsArray = Object.values(grouped).map((divisionData) => {
      // Urutkan members berdasarkan prioritas jabatan.
      // Koordinator akan otomatis berada di paling depan (index 0).
      const sortedMembers = divisionData.members.sort((a, b) => {
        return getPositionPriority(a.jabatan) - getPositionPriority(b.jabatan);
      });

      // Tentukan koordinator (anggota pertama setelah diurutkan)
      const koordinator = sortedMembers[0];

      return {
        divisi: divisionData.divisi,
        deskripsi: divisionData.deskripsi, // Lewatkan deskripsi divisi ke objek akhir
        members: sortedMembers,
        koordinator,
      };
    });

    // Urutkan divisi: Ketua -> Sekretaris -> Bendahara -> Divisi Lain (alfabetis)
    divisions.value = divisionsArray.sort((a, b) => {
      const priorityA = getDivisionPriority(a.divisi);
      const priorityB = getDivisionPriority(b.divisi);

      if (priorityA !== priorityB) {
        return priorityA - priorityB;
      }
      
      // Jika prioritas sama (misal: dua divisi non-BPH), urutkan secara alfabetis
      return a.divisi.localeCompare(b.divisi);
    });

  } catch (err) {
    console.error('❌ Error loading pengurus:', err);
    error.value = err.message || "Gagal mengambil data";
  } finally {
    loading.value = false;
  }
});

// Modal handling
const openDetail = (division) => {
  // Set index ke 0 saat modal dibuka, untuk memulai dari koordinator
  selectedDivision.value = { ...division, currentIndex: 0 };
};

const closeModal = () => {
  selectedDivision.value = null;
};

// Fungsi navigasi slider
const nextMember = () => {
  if (!selectedDivision.value) return;
  const totalMembers = selectedDivision.value.members.length;
  selectedDivision.value.currentIndex = (selectedDivision.value.currentIndex + 1) % totalMembers;
};

const prevMember = () => {
  if (!selectedDivision.value) return;
  const totalMembers = selectedDivision.value.members.length;
  selectedDivision.value.currentIndex = (selectedDivision.value.currentIndex - 1 + totalMembers) % totalMembers;
};

const selectMemberByDot = (index) => {
  if (!selectedDivision.value) return;
  selectedDivision.value.currentIndex = index;
};
</script>

<template>
  <div class="members-container">
    <h1>Struktur Pengurus KBMK UNMUL</h1>
    <p>Periode 2024/2025</p>

    <div v-if="loading" class="loading">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>
    
    <div v-else-if="error" class="error">
      <i class="fas fa-exclamation-circle"></i> {{ error }}
    </div>

    <div v-else-if="divisions.length === 0" class="empty">
      <i class="fas fa-inbox"></i>
      <p>Belum ada data pengurus</p>
    </div>

    <div v-else class="members-grid">
      <div
        v-for="division in divisions"
        :key="division.divisi"
        class="member-card"
      >
        <!-- FOTO KOORDINATOR (selalu anggota pertama) -->
        <img
          :src="getPhotoUrl(division.koordinator.foto)"
          :alt="`Foto ${division.koordinator.nama}`"
          class="card-image"
          @error="(e) => e.target.src = 'https://i.pravatar.cc/300?d=mp'"
        />
        
        <div class="card-content">
          <h3>{{ division.divisi }}</h3>
          <p class="card-meta">
            <span class="member-name">{{ division.koordinator.nama }}</span>
            <span class="member-position">{{ division.koordinator.jabatan }}</span>
            <span v-if="division.members.length > 1" class="member-count">
              <i class="fas fa-users"></i> {{ division.members.length }} anggota
            </span>
          </p>
        </div>
        
        <button @click="openDetail(division)" class="detail-button">
          Detail
        </button>
      </div>
    </div>

    <!-- MODAL DETAIL DENGAN SLIDER -->
    <div v-if="selectedDivision" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <h2>{{ selectedDivision.divisi }}</h2>
        
        <div class="slider-container">
          <!-- Slider Navigasi Panah -->
          <button 
            v-if="selectedDivision.members.length > 1"
            @click="prevMember" 
            class="slider-arrow slider-arrow-left"
          >
            ‹
          </button>
          <button 
            v-if="selectedDivision.members.length > 1"
            @click="nextMember" 
            class="slider-arrow slider-arrow-right"
          >
            ›
          </button>

          <!-- Gambar dan Info Anggota Saat Ini -->
          <div class="slide">
            <img
              :src="getPhotoUrl(selectedDivision.members[selectedDivision.currentIndex].foto)"
              :alt="`Foto ${selectedDivision.members[selectedDivision.currentIndex].nama}`"
              class="modal-image"
              @error="(e) => e.target.src = 'https://i.pravatar.cc/300?d=mp'"
            />
            <h3>{{ selectedDivision.members[selectedDivision.currentIndex].nama }}</h3>
            <p class="modal-position">
              {{ selectedDivision.members[selectedDivision.currentIndex].jabatan }}
            </p>
            <p class="modal-description">
              {{ selectedDivision.members[selectedDivision.currentIndex].deskripsi || 'Tidak ada deskripsi' }}
            </p>
          </div>
          
          <!-- Slider Indicator Dots -->
          <div v-if="selectedDivision.members.length > 1" class="slider-dots">
            <span 
              v-for="(member, index) in selectedDivision.members" 
              :key="member.id"
              :class="['dot', { active: index === selectedDivision.currentIndex }]"
              @click="selectMemberByDot(index)"
            ></span>
          </div>
        </div>

        <!-- BAGIAN DESKRIPSI DIVISI -->
        <div class="division-description-section">
            <h4>Tentang Divisi {{ selectedDivision.divisi }}</h4>
            <p>
              {{ selectedDivision.deskripsi || 'Tidak ada deskripsi divisi.' }}
            </p>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Container & Layout */
.members-container {
  padding: 1rem;
  background-color: #f9f9f9;
  min-height: 100vh;
}

h1 {
  text-align: center;
  margin-bottom: 0.5rem;
  color: #2c3e50;
  font-size: 2rem;
}

.members-container > p {
  text-align: center;
  color: #555;
  margin-bottom: 2.5rem;
}

/* Grid Layout */
.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* Card Styles */
.member-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s, box-shadow 0.3s;
  position: relative;
}

.member-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

/* PERUBAAN DIMULAI DI SINI */
.card-content {
  padding: 1rem;
  flex-grow: 1;
  text-align: center; /* Menengahkan semua teks di dalam card-content */
}

.card-content h3 {
  margin: 0 0 0.5rem;
  color: #2c3e50;
  font-size: 1.2rem;
}

.card-meta {
  font-size: 0.9rem;
  color: #777;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: center; /* Menengahkan item-item di dalam card-meta */
}

.member-name {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
}

.member-position {
  color: #3fa8e5;
  font-weight: 500;
}

.member-count {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
/* PERUBAAN BERAKHIR DI SINI */

.detail-button {
  width: auto;
  align-self: center; /* Menengahkan tombol */
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

/* Modal Styles */
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
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

/* Slider Styles */
.slider-container {
  position: relative;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.slide {
  width: 100%;
  text-align: center;
}

.modal-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.slide h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 1.5rem;
}

.modal-position {
  color: #3fa8e5;
  font-weight: 600;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.modal-description {
  line-height: 1.6;
  color: #555;
  text-align: left;
  background-color: #f4f4f9;
  padding: 1rem;
  border-radius: 4px;
  margin-top: 1rem;
}

.slider-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.8);
  border: none;
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: background-color 0.3s, transform 0.3s;
  z-index: 10;
}

.slider-arrow:hover {
  background: #fff;
  transform: translateY(-50%) scale(1.1);
}

.slider-arrow-left {
  left: 10px;
}

.slider-arrow-right {
  right: 10px;
}

.slider-dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 1rem;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #ccc;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.dot:hover {
  background: #aaa;
}

.dot.active {
  background: #3fa8e5;
  transform: scale(1.2);
}

/* STYLE UNTUK DESKRIPSI DIVISI */
.division-description-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
  text-align: left;
}

.division-description-section h4 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
}

.division-description-section p {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.6;
}

/* Loading, Error, Empty States */
.loading, .error, .empty {
  text-align: center;
  padding: 3rem 1rem;
  font-size: 1.1rem;
}

.loading { color: #007bce; }
.error { color: #f44336; }
.empty { color: #666; }

.loading i, .error i, .empty i {
  font-size: 2rem;
  display: block;
  margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  h1 { font-size: 1.75rem; }
  .members-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
  .modal-image { height: 250px; }
}

@media (max-width: 480px) {
  .members-container { padding: 0.5rem; }
  .members-grid { grid-template-columns: 1fr; }
  .modal-content { padding: 1.5rem; }
  .modal-image { height: 220px; }
  .slider-arrow { font-size: 1.5rem; width: 40px; height: 40px;}
}
</style>