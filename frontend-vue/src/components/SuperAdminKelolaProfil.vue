<template>
  <div class="profil-container">
    <div class="page-header">
      <h1>Profil KBMK</h1>
      <!-- Tombol Edit hanya muncul untuk Admin & Superadmin -->
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Profil
      </button>
    </div>

    <!-- Tampilkan indikator loading -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <!-- Tampilkan pesan error jika ada -->
    <div v-else-if="errorMessage" class="error-message">
      <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
      <button class="btn btn-primary" @click="fetchProfil">Coba Lagi</button>
    </div>

    <!-- Tampilkan konten hanya jika tidak loading dan tidak ada error -->
    <div v-else class="profil-content">
      <!-- Tampilkan pesan jika data profil kosong -->
      <div v-if="isProfilEmpty" class="empty-state">
        <i class="fas fa-info-circle"></i>
        <p>Profil KBMK belum ditambahkan.</p>
        <button v-if="auth.isAdmin" class="btn btn-primary" @click="openModal('edit')">
          <i class="fas fa-plus"></i> Tambah Profil Sekarang
        </button>
      </div>

      <!-- Tampilkan data profil jika ada -->
      <div v-else>
        <h2>{{ profil.judul }}</h2>
        <div class="profil-description">
          <p>{{ profil.deskripsi }}</p>
        </div>
      </div>
    </div>

    <!-- Modal untuk Edit/Tambah Profil -->
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
              <label for="judul">Nama Organisasi</label>
              <input 
                type="text" 
                id="judul" 
                v-model="formData.judul" 
                :disabled="isSaving"
                required 
              />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi Profil</label>
              <textarea 
                id="deskripsi" 
                v-model="formData.deskripsi" 
                :disabled="isSaving"
                required 
                rows="12"
              ></textarea>
              <p class="photo-hint">*Tuliskan deskripsi lengkap mengenai organisasi Anda di sini.</p>
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

// --- STATE UNTUK PROFIL (Disederhanakan) ---
const profil = ref({ id: null, judul: '', deskripsi: '' })
const isLoading = ref(false)
const errorMessage = ref('')
const showModal = ref(false)
const isSaving = ref(false)
const formData = ref({ id: null, judul: '', deskripsi: '' })

// --- COMPUTED ---
const modalTitle = computed(() => {
  // Judul modal dinamis berdasarkan apakah data sudah ada atau belum
  return profil.value.id ? 'Edit Profil KBMK' : 'Tambah Profil KBMK'
})

// PERUBAHAN: Computed property untuk mengecek apakah profil kosong
const isProfilEmpty = computed(() => {
  // Profil dianggap kosong jika tidak ada judul atau deskripsi
  return !profil.value.judul && !profil.value.deskripsi;
})

// --- METHODS ---
const openModal = (mode) => {
  // Salin data profil ke form untuk diedit
  formData.value = { ...profil.value };
  showModal.value = true;
}

const closeModal = () => {
  showModal.value = false;
  errorMessage.value = '';
}

// --- API METHODS (MENGGUNAKAN auth.api) ---
const fetchProfil = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    // PERUBAHAN 2: Gunakan auth.api yang sudah dikonfigurasi
    const response = await auth.api.get('/profile-descs');
    const allProfiles = response.data.data;

    // Cari data profil yang jenisnya 'about'
    const aboutProfile = allProfiles.find(item => item.jenis === 'about');

    if (aboutProfile) {
      profil.value = aboutProfile;
    } else {
      // Jika tidak ada, kosongkan state
      profil.value = { id: null, judul: '', deskripsi: '' };
    }
  } catch (err) {
    console.error('Error fetching profile:', err);
    errorMessage.value = err.response?.data?.message || 'Gagal memuat data profil. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
}

const handleSave = async () => {
  isSaving.value = true;
  errorMessage.value = '';

  try {
    const payload = {
      judul: formData.value.judul,
      deskripsi: formData.value.deskripsi,
      jenis: 'about' // PERUBAHAN 3: Selalu kirim jenis 'about'
    };

    let response;
    if (profil.value.id) {
      // Jika ID ada, lakukan update (PUT)
      response = await auth.api.put(`/profile-descs/${profil.value.id}`, payload);
    } else {
      // Jika tidak ada ID, lakukan create baru (POST)
      response = await auth.api.post('/profile-descs', payload);
    }

    // Update state dengan data terbaru dari server
    profil.value = response.data.data;
    
    closeModal();
    alert('Profil KBMK berhasil disimpan!');
  } catch (err) {
    console.error('Error saving profile:', err);
    if (err.response?.status === 422 && err.response.data.errors) {
      const errors = err.response.data.errors;
      let errorMsg = 'Terjadi kesalahan validasi:\n';
      for (const field in errors) {
        errorMsg += `${errors[field].join(', ')}\n`;
      }
      errorMessage.value = errorMsg;
    } else {
      errorMessage.value = err.response?.data?.message || 'Gagal menyimpan profil. Periksa izin Anda.';
    }
  } finally {
    isSaving.value = false;
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchProfil();
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

/* --- Loading and Error States --- */
.loading-indicator, .error-message {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #007bce;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
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
  white-space: pre-wrap; /* Menjaga format paragraf dari textarea */
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
.form-group input[type="text"],
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
.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #007bce;
  box-shadow: 0 0 0 3px rgba(0, 123, 206, 0.25);
}
.form-group input:disabled,
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
</style>