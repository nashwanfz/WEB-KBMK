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
  return `http://localhost:8000/storage/${foto}`;
};

// Fungsi untuk menentukan prioritas jabatan
const getPositionPriority = (jabatan) => {
  if (!jabatan) return 999;
  
  const j = jabatan.toLowerCase();
  
  // BPH Inti (prioritas tertinggi)
  if (j.includes('ketua umum') || j === 'ketua') return 1;
  if (j.includes('sekretaris')) return 2;
  if (j.includes('bendahara')) return 3;
  
  // Koordinator divisi
  if (j.includes('koordinator') || j.includes('koor')) return 10;
  if (j.includes('wakil')) return 11;
  
  // Staff/anggota
  if (j.includes('staff') || j.includes('anggota')) return 20;
  
  return 15; // Default
};

// Fungsi untuk menentukan apakah seseorang adalah koordinator
const isKoordinator = (member) => {
  if (!member.jabatan) return false;
  const j = member.jabatan.toLowerCase();
  return j.includes('koordinator') || j.includes('koor');
};

// Fungsi untuk menentukan apakah seseorang adalah BPH Inti
const isBPHInti = (member) => {
  if (!member.jabatan) return false;
  const j = member.jabatan.toLowerCase();
  return j.includes('ketua') || j.includes('sekretaris') || j.includes('bendahara');
};

// Ambil data pengurus
onMounted(async () => {
  try {
    const res = await axios.get("http://localhost:8000/api/pengurus");
    const data = res.data.data;

    console.log('📦 Raw API Data:', data);

    // Kelompokkan pengurus berdasarkan division.nama
    const grouped = {};
    data.forEach((item) => {
      const divisionName = item.division?.nama || 'Tanpa Divisi';
      
      if (!grouped[divisionName]) {
        grouped[divisionName] = [];
      }
      grouped[divisionName].push(item);
    });

    console.log('📊 Grouped Data:', grouped);

    // Buat array divisions
    const divisionsArray = Object.entries(grouped).map(([divisionName, members]) => {
      // Urutkan members berdasarkan prioritas jabatan
      const sortedMembers = members.sort((a, b) => {
        return getPositionPriority(a.jabatan) - getPositionPriority(b.jabatan);
      });

      // Tentukan koordinator
      // 1. Cari yang jabatannya mengandung "koordinator"
      // 2. Jika tidak ada, ambil yang pertama (prioritas tertinggi)
      const koordinator = sortedMembers.find(m => isKoordinator(m)) || sortedMembers[0];

      return {
        divisi: divisionName,
        members: sortedMembers,
        currentIndex: 0,
        koordinator,
      };
    });

    // Urutkan divisi: BPH Inti (Ketua/Sekretaris/Bendahara) di depan
    divisions.value = divisionsArray.sort((a, b) => {
      const aHasBPH = a.members.some(m => isBPHInti(m));
      const bHasBPH = b.members.some(m => isBPHInti(m));
      
      // Divisi yang punya BPH Inti di depan
      if (aHasBPH && !bHasBPH) return -1;
      if (!aHasBPH && bHasBPH) return 1;
      
      // Sisanya urutkan alfabetis
      return a.divisi.localeCompare(b.divisi);
    });

    console.log('✅ Final Divisions (Sorted):', divisions.value);
  } catch (err) {
    console.error('❌ Error loading pengurus:', err);
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

// Fungsi untuk format jabatan
const getJabatanDisplay = (member) => {
  if (!member.jabatan) return '';
  return member.jabatan;
};
</script>

<template>
  <div class="profile-page">
    <h1 class="title">Struktur Pengurus KBMK UNMUL</h1>
    <p class="subtitle">Periode 2024/2025</p>

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

    <div v-else class="division-container">
      <div
        v-for="division in divisions"
        :key="division.divisi"
        class="division-card"
      >
        <div class="image-container">
          <img
            :src="getPhotoUrl(division.members[division.currentIndex].foto)"
            :alt="`Foto ${division.members[division.currentIndex].nama}`"
            class="division-image"
            @error="(e) => e.target.src = 'https://i.pravatar.cc/300?d=mp'"
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
          
          <!-- Indicator dots -->
          <div v-if="division.members.length > 1" class="dots">
            <span 
              v-for="(member, idx) in division.members" 
              :key="member.id"
              :class="['dot', { active: idx === division.currentIndex }]"
              @click="division.currentIndex = idx"
            ></span>
          </div>

          <!-- Badge untuk BPH Inti -->
          <div 
            v-if="isBPHInti(division.members[division.currentIndex])" 
            class="bph-badge"
          >
            BPH INTI
          </div>
        </div>

        <div class="division-info">
          <h3>{{ division.divisi }}</h3>

          <!-- Tampilkan nama dan jabatan current member -->
          <div class="member-preview">
            <p class="member-name">
              {{ division.members[division.currentIndex].nama }}
            </p>
            <p v-if="division.members[division.currentIndex].jabatan" class="member-jabatan">
              {{ getJabatanDisplay(division.members[division.currentIndex]) }}
            </p>
          </div>

          <!-- Info jumlah anggota jika > 1 -->
          <p v-if="division.members.length > 1" class="member-count">
            <i class="fas fa-users"></i> {{ division.members.length }} anggota
          </p>
        </div>

        <!-- Tombol detail -->
        <button class="detail-btn" @click="openDetail(division)">
          <i class="fas fa-info-circle"></i> Detail
        </button>
      </div>
    </div>

    <!-- Modal Detail -->
    <div v-if="selectedDivision" class="modal-overlay" @click="closeDetail">
      <div class="modal-content" @click.stop>
        <button class="close-btn" @click="closeDetail">
          <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-header-info">
          <h2>{{ selectedDivision.divisi }}</h2>
          <span 
            v-if="isBPHInti(selectedDivision.members[selectedDivision.currentIndex])"
            class="bph-badge-modal"
          >
            BPH INTI
          </span>
        </div>

        <div class="carousel">
          <img
            :src="getPhotoUrl(selectedDivision.members[selectedDivision.currentIndex].foto)"
            :alt="`Foto ${selectedDivision.members[selectedDivision.currentIndex].nama}`"
            class="detail-image"
            @error="(e) => e.target.src = 'https://i.pravatar.cc/300?d=mp'"
          />
          <button 
            v-if="selectedDivision.members.length > 1"
            class="arrow left" 
            @click="prevDetail"
          >‹</button>
          <button 
            v-if="selectedDivision.members.length > 1"
            class="arrow right" 
            @click="nextDetail"
          >›</button>
        </div>

        <div class="member-info">
          <h3>
            {{ selectedDivision.members[selectedDivision.currentIndex].nama }}
          </h3>
          
          <p v-if="selectedDivision.members[selectedDivision.currentIndex].jabatan" class="jabatan-detail">
            <i class="fas fa-id-badge"></i> 
            <strong>{{ getJabatanDisplay(selectedDivision.members[selectedDivision.currentIndex]) }}</strong>
          </p>
          
          <p class="deskripsi">
            {{ selectedDivision.members[selectedDivision.currentIndex].deskripsi || 'Tidak ada deskripsi' }}
          </p>
          
          <!-- Navigation info -->
          <div v-if="selectedDivision.members.length > 1" class="nav-info">
            <button 
              v-for="(member, idx) in selectedDivision.members" 
              :key="member.id"
              @click="selectedDivision.currentIndex = idx"
              :class="['member-nav-btn', { active: idx === selectedDivision.currentIndex }]"
            >
              {{ member.nama.split(' ')[0] }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* LAYOUT HALAMAN */
.profile-page {
  padding: 2rem 1.5rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  min-height: 100vh;
}

/* JUDUL */
.title {
  color: #2b1560;
  text-align: center;
  margin-bottom: 0.5rem;
  font-size: 1.75rem;
  font-weight: 700;
}

.subtitle {
  text-align: center;
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 2rem;
}

/* LOADING & ERROR */
.loading, .error, .empty {
  text-align: center;
  padding: 3rem 1rem;
  font-size: 1.1rem;
}

.loading {
  color: #007bce;
}

.error {
  color: #f44336;
}

.empty {
  color: #666;
}

.loading i, .error i, .empty i {
  font-size: 2rem;
  display: block;
  margin-bottom: 1rem;
}

/* GRID */
.division-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 300px));
  justify-content: center;
  gap: 1.5rem;
  padding: 0 0.5rem;
}

/* CARD */
.division-card {
  position: relative;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(12, 15, 30, 0.08);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding-bottom: 3rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.division-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(12, 15, 30, 0.15);
}

/* GAMBAR */
.image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.division-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s ease;
}

.division-card:hover .division-image {
  transform: scale(1.08);
}

/* BPH BADGE */
.bph-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
  z-index: 10;
}

/* PANAH */
.arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.95);
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem 0.75rem;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
  z-index: 10;
  color: #2b1560;
}

.arrow:hover {
  background: white;
  transform: translateY(-50%) scale(1.15);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.arrow.left {
  left: 10px;
}

.arrow.right {
  right: 10px;
}

/* DOTS INDICATOR */
.dots {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  z-index: 10;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  transition: all 0.3s ease;
  cursor: pointer;
  border: 2px solid transparent;
}

.dot:hover {
  background: rgba(255, 255, 255, 0.8);
}

.dot.active {
  background: white;
  transform: scale(1.3);
  border-color: rgba(255, 255, 255, 0.5);
}

/* INFO DIVISI */
.division-info {
  padding: 1rem 1.25rem 1rem;
  text-align: center;
}

.division-info h3 {
  margin: 0 0 0.75rem 0;
  color: #2b1560;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.3;
}

.member-preview {
  margin-bottom: 0.5rem;
}

.member-name {
  margin: 0;
  color: #333;
  font-size: 0.95rem;
  font-weight: 600;
}

.member-jabatan {
  margin: 4px 0 0;
  color: #3fa8e5;
  font-size: 0.85rem;
  font-weight: 500;
}

.member-count {
  margin: 8px 0 0;
  color: #888;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

/* TOMBOL DETAIL */
.detail-btn {
  position: absolute;
  bottom: 0.85rem;
  right: 0.85rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.6rem 1.1rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.detail-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
  backdrop-filter: blur(4px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 20px;
  padding: 1.75rem;
  position: relative;
  width: 92%;
  max-width: 580px;
  max-height: 90vh;
  overflow-y: auto;
  text-align: center;
  animation: slideUp 0.3s ease;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(40px) scale(0.95);
  }
  to { 
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  border: none;
  background: rgba(0, 0, 0, 0.08);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
}

.close-btn:hover {
  background: rgba(0, 0, 0, 0.15);
  color: #333;
  transform: rotate(90deg);
}

.modal-header-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.modal-header-info h2 {
  color: #2b1560;
  margin: 0;
  font-size: 1.6rem;
}

.bph-badge-modal {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  padding: 0.4rem 0.9rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.carousel {
  position: relative;
  margin-bottom: 1.5rem;
}

.detail-image {
  width: 100%;
  height: 380px;
  object-fit: cover;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.member-info h3 {
  color: #2b1560;
  margin-bottom: 0.75rem;
  font-size: 1.4rem;
}

.jabatan-detail {
  color: #3fa8e5;
  font-size: 1rem;
  margin: 0.75rem 0 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.jabatan-detail strong {
  font-weight: 600;
}

.deskripsi {
  color: #555;
  font-size: 0.95rem;
  line-height: 1.7;
  margin-top: 1rem;
  text-align: left;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
  border-left: 4px solid #3fa8e5;
}

.nav-info {
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid #eee;
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.member-nav-btn {
  padding: 0.5rem 1rem;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  color: #666;
  transition: all 0.2s ease;
}

.member-nav-btn:hover {
  border-color: #3fa8e5;
  color: #3fa8e5;
}

.member-nav-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .title {
    font-size: 1.35rem;
  }

  .division-container {
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
  }

  .detail-image {
    height: 300px;
  }
}

@media (max-width: 480px) {
  .profile-page {
    padding: 1.5rem 1rem;
  }

  .division-container {
    grid-template-columns: 1fr;
  }

  .image-container {
    height: 180px;
  }

  .modal-content {
    padding: 1.25rem;
  }

  .detail-image {
    height: 260px;
  }
}
</style>