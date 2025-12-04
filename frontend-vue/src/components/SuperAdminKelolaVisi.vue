<template>
  <div class="visi-misi-container">
    <div class="page-header">
      <h1>Visi & Misi</h1>
      <!-- PERUBAHAN: Tombol Edit hanya muncul untuk Admin -->
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Visi & Misi
      </button>
    </div>

    <!-- Tampilkan indikator loading -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <!-- PERUBAHAN: Tampilkan pesan error jika ada -->
    <div v-else-if="errorMessage" class="error-message">
      <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
      <button class="btn btn-primary" @click="fetchVisiMisi">Coba Lagi</button>
    </div>

    <!-- Tampilkan konten jika tidak loading dan tidak ada error -->
    <div v-else class="visi-misi-content">
      <!-- Tampilkan pesan jika data kosong -->
      <div v-if="isDataEmpty" class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>Visi & Misi belum ditambahkan.</p>
        <!-- PERUBAHAN: Tombol Tambah hanya muncul untuk Admin -->
        <button v-if="auth.isAdmin" class="btn btn-primary" @click="openModal('edit')">
          <i class="fas fa-plus"></i> Tambah Sekarang
        </button>
      </div>

      <!-- Tampilkan data jika ada -->
      <div v-else>
        <div class="content-section">
          <h2>Visi</h2>
          <div class="section-text">
            <p>{{ visiMisi.visi.deskripsi }}</p>
          </div>
        </div>

        <div class="content-section">
          <h2>Misi</h2>
          <div class="section-text">
            <!-- Menampilkan misi sebagai poin-poin -->
            <ul>
              <li v-for="(point, index) in visiMisiWithParagraphs.misi" :key="`m-${index}`">
                {{ point }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal untuk Edit Visi & Misi -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ modalTitle }}</h3>
          <button class="close-btn" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSave">
            <div class="form-group">
              <label for="visi">Visi</label>
              <textarea id="visi" v-model="formData.visiText" :disabled="isSaving" required rows="5"></textarea>
              <p class="photo-hint">*Tuliskan visi organisasi.</p>
            </div>
            <div class="form-group">
              <label for="misi">Misi</label>
              <textarea id="misi" v-model="formData.misiText" :disabled="isSaving" required rows="8"></textarea>
              <p class="photo-hint">*Tuliskan misi organisasi, pisah setiap poin dengan baris baru.</p>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isSaving">Batal</button>
          <button type="submit" class="btn btn-primary" @click="handleSave" :disabled="isSaving">
            <span v-if="isSaving">
              <i class="fas fa-spinner fa-spin"></i> Menyimpan...
            </span>
            <span v-else>Simpan Perubahan</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'

// --- PERUBAHAN 1: INJECT DATA DARI APP.VUE ---
const auth = inject('auth');

// --- STATE ---
// Struktur data untuk menyimpan ID dan deskripsi
const visiMisi = ref({
  visi: { id: null, deskripsi: '' },
  misi: { id: null, deskripsi: '' }
})

const isLoading = ref(false) // Untuk fetch data awal
const isSaving = ref(false)  // Untuk proses simpan
const errorMessage = ref('')
const showModal = ref(false)
const formData = ref({
  visiText: '',
  misiText: ''
})

// --- COMPUTED ---
const modalTitle = computed(() => {
  return 'Edit Visi & Misi'
})

// Computed untuk memecah misi menjadi poin-poin
const visiMisiWithParagraphs = computed(() => {
  return {
    visi: visiMisi.value.visi.deskripsi.split(/\n\s*\n/).filter(p => p.trim() !== ''),
    misi: visiMisi.value.misi.deskripsi.split('\n').filter(p => p.trim() !== '')
  }
})

// Computed untuk mengecek apakah data kosong
const isDataEmpty = computed(() => {
  return !visiMisi.value.visi.deskripsi && !visiMisi.value.misi.deskripsi;
})

// --- METHODS ---
const openModal = (mode) => {
  // Salin data ke form untuk diedit
  formData.value = {
    visiText: visiMisi.value.visi.deskripsi,
    misiText: visiMisi.value.misi.deskripsi
  }
  showModal.value = true;
}

const closeModal = () => {
  showModal.value = false;
  errorMessage.value = '';
}

// --- API METHODS (MENGGUNAKAN auth.api) ---
const fetchVisiMisi = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    // PERUBAHAN 2: Gunakan auth.api
    const response = await auth.api.get('/profile-descs');
    const data = response.data.data;

    // Cari data visi dan misi
    const visiData = data.find(item => item.jenis === 'visi');
    const misiData = data.find(item => item.jenis === 'misi');

    visiMisi.value = {
      visi: { id: visiData ? visiData.id : null, deskripsi: visiData ? visiData.deskripsi : '' },
      misi: { id: misiData ? misiData.id : null, deskripsi: misiData ? misiData.deskripsi : '' }
    };
  } catch (err) {
    console.error('Error fetching visi misi:', err);
    errorMessage.value = err.response?.data?.message || 'Gagal memuat data. Periksa izin Anda.';
  } finally {
    isLoading.value = false;
  }
}

const handleSave = async () => {
  isSaving.value = true;
  errorMessage.value = '';

  try {
    const visiPayload = { deskripsi: formData.value.visiText, jenis: 'visi', judul: 'Visi' };
    const misiPayload = { deskripsi: formData.value.misiText, jenis: 'misi', judul: 'Misi' };

    const updatePromises = [];

    if (visiMisi.value.visi.id) {
      updatePromises.push(auth.api.put(`/profile-descs/${visiMisi.value.visi.id}`, visiPayload));
    } else {
      updatePromises.push(auth.api.post('/profile-descs', visiPayload));
    }

    if (visiMisi.value.misi.id) {
      updatePromises.push(auth.api.put(`/profile-descs/${visiMisi.value.misi.id}`, misiPayload));
    } else {
      updatePromises.push(auth.api.post('/profile-descs', misiPayload));
    }

    await Promise.all(updatePromises);

    closeModal();
    alert('Visi & Misi berhasil diperbarui!');
    await fetchVisiMisi(); // Ambil ulang data untuk memastikan tampilan terbaru
  } catch (err) {
    console.error('Error saving visi misi:', err);
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan data. Periksa izin Anda.';
  } finally {
    isSaving.value = false;
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchVisiMisi();
})
</script>

<style scoped>
/* --- General Layout --- */
.visi-misi-container {
  padding: 1.5rem;
  background-color: #f4f6f9;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-header h1 {
  color: #2c3e50;
  margin: 0;
  font-size: 2rem;
}

/* --- Loading and Error States --- */
.loading-indicator, .error-message {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
}

.loading-indicator {
  color: #007bce;
}

.error-message {
  color: #F44336;
}

.error-message i {
  margin-right: 0.5rem;
}

/* --- Empty State --- */
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  max-width: 600px; /* Batasi lebar agar lebih rapi */
  margin: 0 auto; /* Pusatkan */
}

.empty-state i {
  font-size: 4rem;
  color: #ced4da;
  margin-bottom: 1rem;
}

.empty-state p {
  font-size: 1.2rem;
  color: #6c757d;
  margin: 0 0 2rem 0;
}

/* --- PERUBAHAN UTAMA: Content Display --- */
.visi-misi-content {
  display: flex;
  flex-direction: column; /* Tumpuk secara vertikal */
  gap: 2rem;
  align-items: center; /* Pusatkan item secara horizontal */
}

.content-section {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  width: 100%; /* Ambil lebar yang tersedia */
  max-width: 800px; /* Tapi batasi lebar maksimal untuk keterbacaan */
}

.content-section h2 {
  color: #007bce;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.section-text p, .section-text ul {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
}

.section-text ul {
  padding-left: 20px;
}

.section-text li {
  margin-bottom: 0.5rem;
}

/* --- Buttons --- */
.btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #007bce;
  color: white;
}
.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}
.btn-secondary:hover:not(:disabled) {
  background-color: #5a6268;
}

/* --- Modal --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #eee;
}
.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}
.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #aaa;
}

.modal-body {
  padding: 1.5rem;
}
.form-group {
  margin-bottom: 1.5rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
}
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  box-sizing: border-box;
}
.form-group textarea:focus {
  outline: none;
  border-color: #007bce;
  box-shadow: 0 0 0 3px rgba(0, 123, 206, 0.25);
}
.form-group textarea:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}
.photo-hint {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.25rem;
  margin-bottom: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}

/* Media query untuk layar lebih kecil tidak lagi diperlukan karena layout sudah vertikal */
</style>