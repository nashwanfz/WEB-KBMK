<template>
  <div class="profil-container">
    <div class="page-header">
      <h1>Profil KBMK</h1>
      <button class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Profil
      </button>
    </div>

    <!-- Tampilkan indikator loading -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <!-- Tampilkan konten hanya jika tidak loading -->
    <div v-else class="profil-content">
      <!-- PERUBAHAN: Tampilkan pesan jika data kosong -->
      <div v-if="isProfilEmpty" class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>Deskripsi profil belum ditambahkan.</p>
        <button class="btn btn-primary" @click="openModal('edit')">
          <i class="fas fa-plus"></i> Tambah Deskripsi Sekarang
        </button>
      </div>

      <!-- Tampilkan data profil jika ada -->
      <div v-else>
        <h2>{{ profil.namaOrganisasi }}</h2>
        <div class="profil-description">
          <p v-for="(paragraf, index) in profil.deskripsi" :key="index">
            {{ paragraf }}
          </p>
        </div>
      </div>
    </div>

    <!-- Modal untuk Edit Profil -->
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
              <label for="namaOrganisasi">Nama Organisasi</label>
              <input type="text" id="namaOrganisasi" v-model="formData.namaOrganisasi" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi Profil</label>
              <textarea id="deskripsi" v-model="formData.deskripsiText" required rows="12"></textarea>
              <p class="photo-hint">*Edit keseluruhan profil di sini. Pastikan format "Tentang Kami", "Visi", dan "Misi" terpisah dengan jelas.</p>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
          <button type="submit" class="btn btn-primary" @click="handleSave" :disabled="isLoading">
            <span v-if="isLoading">
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
import axios from 'axios'

// --- INJECT AUTH DAN KONFIGURASI AXIOS ---
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
const profil = ref({
  namaOrganisasi: 'Keluarga Besar Mahasiswa Kristen (KBMK)',
  deskripsi: [], // Akan diisi array paragraf
  profileIds: {}
})
const isLoading = ref(false)
const errorMessage = ref('')
const showModal = ref(false)
const modalMode = ref('edit')
const formData = ref({
  namaOrganisasi: '',
  deskripsiText: ''
})

// --- COMPUTED ---
const modalTitle = computed(() => {
  return modalMode.value === 'edit' ? 'Edit Profil KBMK' : 'Profil KBMK'
})

// PERUBAHAN: Computed property untuk mengecek apakah profil kosong
const isProfilEmpty = computed(() => {
  // Profil dianggap kosong jika array deskripsi kosong
  return profil.value.deskripsi.length === 0;
})

// --- METHODS ---
const fetchProfil = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await api.get('/profile-descs')
    const data = response.data.data

    let namaOrg = 'Keluarga Besar Mahasiswa Kristen (KBMK)';
    let deskripsiGabung = [];
    const ids = {};

    // Urutkan data agar konsisten (about, visi, misi)
    const orderedData = ['about', 'visi', 'misi'].map(jenis => 
      data.find(item => item.jenis === jenis)
    ).filter(Boolean);

    orderedData.forEach(item => {
      ids[item.jenis] = item.id;
      if (item.jenis === 'about') {
        namaOrg = item.judul || namaOrg;
      }
      const paragraf = item.deskripsi.split(/\n\s*\n/).filter(p => p.trim() !== '');
      deskripsiGabung = deskripsiGabung.concat(paragraf);
    });

    profil.value = {
      namaOrganisasi: namaOrg,
      deskripsi: deskripsiGabung,
      profileIds: ids
    };

  } catch (error) {
    console.error('Error fetching profile:', error)
    if (error.response && error.response.status === 401) {
      errorMessage.value = 'Sesi Anda telah berakhir. Silakan login kembali.';
      auth.handleLogout();
    } else {
      errorMessage.value = 'Gagal memuat data profil. Periksa konsol untuk detail.'
    }
  } finally {
    isLoading.value = false
  }
}

const openModal = (mode) => {
  modalMode.value = mode
  // Jika data kosong, beri teks awal yang membantu
  const initialText = isProfilEmpty.value 
    ? "Tentang Kami:\n\nVisi:\n\nMisi:"
    : profil.value.deskripsi.join('\n\n');

  formData.value = {
    namaOrganisasi: profil.value.namaOrganisasi,
    deskripsiText: initialText
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const textBlocks = formData.value.deskripsiText.split(/\n\s*\n/);
    
    const updates = [
      { jenis: 'about', deskripsi: textBlocks[0] || '', judul: formData.value.namaOrganisasi },
      { jenis: 'visi', deskripsi: textBlocks[1] || '' },
      { jenis: 'misi', deskripsi: textBlocks[2] || '' }
    ];

    const updatePromises = updates.map(update => {
      const id = profil.value.profileIds[update.jenis];
      
      // Jika ID tidak ada (data baru), buat baru dengan POST
      if (!id) {
        const payload = { ...update };
        return api.post('/profile-descs', payload);
      }
      
      // Jika ID ada, update dengan PUT
      const payload = { deskripsi: update.deskripsi };
      if (update.jenis === 'about') {
        payload.judul = update.judul;
      }
      return api.put(`/profile-descs/${id}`, payload);
    });

    await Promise.all(updatePromises);

    alert('Profil KBMK berhasil diperbarui!');
    closeModal();
    await fetchProfil(); // Ambil ulang data untuk memastikan tampilan terbaru

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
      errorMessage.value = 'Gagal menyimpan profil. Periksa konsol untuk detail.'
    }
  } finally {
    isLoading.value = false
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchProfil()
})
</script>

<style scoped>
/* --- General Layout --- */
.profil-container {
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

/* --- Loading Indicator --- */
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
.profil-content {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.profil-content h2 {
  color: #007bce;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.profil-description p {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
  text-align: justify;
  margin-bottom: 1rem;
}

.profil-description p:last-child {
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
.form-group input[type="text"],
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
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
</style>