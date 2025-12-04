<template>
  <div class="visi-misi-container">
    <div class="page-header">
      <h1>Visi & Misi</h1>
      <button class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Visi & Misi
      </button>
    </div>

    <!-- Tampilkan indikator loading -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <!-- Tampilkan konten jika tidak loading -->
    <div v-else class="visi-misi-content">
      <!-- PERUBAHAN: Tampilkan pesan jika data kosong -->
      <div v-if="isDataEmpty" class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>Visi & Misi belum ditambahkan.</p>
        <button class="btn btn-primary" @click="openModal('edit')">
          <i class="fas fa-plus"></i> Tambah Sekarang
        </button>
      </div>

      <!-- Tampilkan data jika ada -->
      <div v-else>
        <div class="content-section">
          <h2>Visi</h2>
          <div class="section-text">
            <p v-for="(paragraf, index) in visiMisiWithParagraphs.visi" :key="`v-${index}`">
              {{ paragraf }}
            </p>
          </div>
        </div>

        <div class="content-section">
          <h2>Misi</h2>
          <div class="section-text">
            <p v-for="(paragraf, index) in visiMisiWithParagraphs.misi" :key="`m-${index}`">
              {{ paragraf }}
            </p>
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
              <textarea id="visi" v-model="formData.visiText" :disabled="modalMode === 'view'" required rows="5"></textarea>
              <p class="photo-hint" v-if="modalMode !== 'view'">*Tuliskan visi organisasi.</p>
            </div>
            <div class="form-group">
              <label for="misi">Misi</label>
              <textarea id="misi" v-model="formData.misiText" :disabled="modalMode === 'view'" required rows="8"></textarea>
              <p class="photo-hint" v-if="modalMode !== 'view'">*Tuliskan misi organisasi, pisah setiap poin dengan baris baru.</p>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleSave" :disabled="isLoading">
              <span v-if="isLoading">
                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
              </span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import axios from 'axios'

// --- INTEGRASI API ---
const auth = inject('auth');
const API_URL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

api.interceptors.request.use(config => {
  if (auth.token.value) {
    config.headers.Authorization = `Bearer ${auth.token.value}`;
  }
  return config;
});

// --- STATE ---
// PERUBAHAN: Struktur data untuk menyimpan ID dan deskripsi
const visiMisi = ref({
  visi: { id: null, deskripsi: '' },
  misi: { id: null, deskripsi: '' }
})

const isLoading = ref(false)
const errorMessage = ref('')
const showModal = ref(false)
const modalMode = ref('edit')
const formData = ref({
  visiText: '',
  misiText: ''
})

// --- COMPUTED ---
const modalTitle = computed(() => {
  return modalMode.value === 'edit' ? 'Edit Visi & Misi' : 'Detail Visi & Misi'
})

// PERUBAHAN: Computed untuk memecah deskripsi menjadi paragraf
const visiMisiWithParagraphs = computed(() => {
  return {
    visi: visiMisi.value.visi.deskripsi.split(/\n\s*\n/).filter(p => p.trim() !== ''),
    misi: visiMisi.value.misi.deskripsi.split('\n').filter(p => p.trim() !== '')
  }
})

// PERUBAHAN: Computed untuk mengecek apakah data kosong
const isDataEmpty = computed(() => {
  return !visiMisi.value.visi.deskripsi && !visiMisi.value.misi.deskripsi;
})

// --- METHODS ---
// PERUBAHAN: Fungsi untuk mengambil data dari API
const fetchVisiMisi = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await api.get('/profile-descs')
    const data = response.data.data

    // Cari data visi dan misi
    const visiData = data.find(item => item.jenis === 'visi');
    const misiData = data.find(item => item.jenis === 'misi');

    visiMisi.value = {
      visi: { id: visiData ? visiData.id : null, deskripsi: visiData ? visiData.deskripsi : '' },
      misi: { id: misiData ? misiData.id : null, deskripsi: misiData ? misiData.deskripsi : '' }
    };

  } catch (error) {
    console.error('Error fetching profile:', error)
    if (error.response && error.response.status === 401) {
      errorMessage.value = 'Sesi Anda telah berakhir. Silakan login kembali.';
      auth.handleLogout();
    } else {
      errorMessage.value = 'Gagal memuat data. Periksa konsol untuk detail.'
    }
  } finally {
    isLoading.value = false
  }
}

const openModal = (mode) => {
  modalMode.value = mode
  formData.value = {
    visiText: visiMisi.value.visi.deskripsi,
    misiText: visiMisi.value.misi.deskripsi
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// PERUBAHAN: Fungsi save yang memperbarui dua record di backend
const handleSave = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const visiPayload = { deskripsi: formData.value.visiText };
    const misiPayload = { deskripsi: formData.value.misiText };

    // Buat array promise untuk update visi dan misi
    const updatePromises = [];

    if (visiMisi.value.visi.id) {
      updatePromises.push(api.put(`/profile-descs/${visiMisi.value.visi.id}`, visiPayload));
    } else {
      // Jika tidak ada ID, buat baru
      updatePromises.push(api.post('/profile-descs', { ...visiPayload, jenis: 'visi', judul: 'Visi' }));
    }

    if (visiMisi.value.misi.id) {
      updatePromises.push(api.put(`/profile-descs/${visiMisi.value.misi.id}`, misiPayload));
    } else {
      // Jika tidak ada ID, buat baru
      updatePromises.push(api.post('/profile-descs', { ...misiPayload, jenis: 'misi', judul: 'Misi' }));
    }

    await Promise.all(updatePromises);

    alert('Visi & Misi berhasil diperbarui!');
    closeModal();
    await fetchVisiMisi(); // Ambil ulang data untuk memastikan tampilan terbaru

  } catch (error) {
    console.error('Error saving profile:', error)
    if (error.response && error.response.status === 422) {
      const errors = error.response.data
      let errorMsg = 'Terjadi kesalahan validasi:\n'
      for (const field in errors) {
        errorMsg += `${errors[field].join(', ')}\n`
      }
      errorMessage.value = errorMsg
    } else {
      errorMessage.value = 'Gagal menyimpan. Periksa konsol untuk detail.'
    }
  } finally {
    isLoading.value = false
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchVisiMisi()
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

/* --- PERUBAHAN: Style untuk loading indicator --- */
.loading-indicator {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #007bce;
}

/* --- PERUBAHAN: Style untuk empty state --- */
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

/* --- Content Display --- */
.visi-misi-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.content-section {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.content-section h2 {
  color: #007bce;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.section-text p {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
  text-align: justify;
  margin-bottom: 1rem;
}

.section-text p:last-child {
  margin-bottom: 0;
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
.btn-secondary:hover {
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

/* Responsive untuk layar lebih kecil */
@media (max-width: 768px) {
  .visi-misi-content {
    grid-template-columns: 1fr;
  }
}
</style>