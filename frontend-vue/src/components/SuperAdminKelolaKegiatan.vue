<template>
  <div class="kelola-kegiatan-container">
    <!-- Notifikasi Sederhana -->
    <div v-if="notification.show" :class="['notification', notification.type]">
      <i :class="notification.icon"></i>
      <span>{{ notification.message }}</span>
      <button class="notification-close" @click="hideNotification">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="page-header">
      <h1>Kelola Kegiatan</h1>
      <!-- PERUBAHAN: Tombol Tambah hanya untuk Admin & Koor Media -->
      <button v-if="auth.isAdmin || auth.isKoorMedia" class="btn btn-primary" @click="openModal('create')" :disabled="isLoading">
        <i class="fas fa-plus"></i> Tambah Kegiatan Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama kegiatan..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <div v-else-if="errorMessage" class="error-message">
      <i class="fas fa-exclamation-circle"></i> {{ errorMessage }}
      <button class="btn btn-primary" @click="fetchKegiatan">Coba Lagi</button>
    </div>

    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Foto Kegiatan</th>
            <th>Nama Kegiatan</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredKegiatan.length === 0">
            <td colspan="4" class="text-center">Tidak ada kegiatan.</td>
          </tr>
          <tr v-for="kegiatan in filteredKegiatan" :key="kegiatan.id">
            <td>
              <img :src="getPhotoUrl(kegiatan.foto_url)" alt="Foto Kegiatan" class="kegiatan-photo" />
            </td>
            <td>{{ kegiatan.nama }}</td>
            <td>{{ kegiatan.deskripsi }}</td>
            <td class="action-buttons">
              <!-- PERUBAHAN: Tombol Edit & Hapus hanya untuk Admin & Koor Media -->
              <button v-if="auth.isAdmin || auth.isKoorMedia" class="btn-icon btn-view" @click="openModal('view', kegiatan)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button v-if="auth.isAdmin || auth.isKoorMedia" class="btn-icon btn-edit" @click="openModal('edit', kegiatan)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button v-if="auth.isAdmin || auth.isKoorMedia" class="btn-icon btn-delete" @click="handleDelete(kegiatan)" title="Hapus" :disabled="isDeleting">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal untuk Tambah, Edit, dan Lihat Detail -->
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
              <label for="nama">Nama Kegiatan</label>
              <input type="text" id="nama" v-model="formData.nama" :disabled="modalMode === 'view' || isSaving" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view' || isSaving" rows="4" required></textarea>
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="foto">Foto Kegiatan</label>
              <input type="file" id="foto" @change="handlePhotoUpload" accept="image/*" />
              <p class="photo-hint">*Pilih file gambar untuk foto kegiatan.</p>
            </div>
            <!-- Tampilkan foto saat ini di mode view/edit -->
            <div class="form-group" v-if="modalMode === 'view' && formData.foto_url">
              <label>Foto Saat Ini</label>
              <img :src="getPhotoUrl(formData.foto_url)" alt="Foto Kegiatan" class="current-photo" />
            </div>
             <!-- Tampilkan preview foto baru di mode edit jika ada -->
            <div class="form-group" v-if="modalMode === 'edit' && formData.foto_url && formData.foto_url.startsWith('data:')">
              <label>Preview Foto Baru</label>
              <img :src="formData.foto_url" alt="Preview Foto Baru" class="current-photo" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isSaving">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleSave" :disabled="isSaving">
              <i v-if="isSaving" class="fas fa-spinner fa-spin"></i>
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'

// --- INTEGRASI API ---
const auth = inject('auth');

// --- STATE ---
const kegiatanList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create')
const formData = ref({
  id: null,
  nama: '',
  deskripsi: '',
  foto: null,
  foto_url: null
})
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const errorMessage = ref('')

// State untuk notifikasi
const notification = ref({ show: false, message: '', type: 'success', icon: 'fas fa-check-circle' })

// --- COMPUTED ---
const filteredKegiatan = computed(() => {
  if (!searchQuery.value) {
    return kegiatanList.value
  }
  const query = searchQuery.value.toLowerCase()
  return kegiatanList.value.filter(kegiatan =>
    kegiatan.nama.toLowerCase().includes(query)
  )
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Kegiatan Baru'
    case 'view': return 'Detail Kegiatan'
    case 'edit': return 'Edit Kegiatan'
    default: return 'Form Kegiatan'
  }
})

// --- METHODS ---
const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type,
    icon: type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'
  }
  setTimeout(hideNotification, 5000)
}

const hideNotification = () => {
  notification.value.show = false
}

const getPhotoUrl = (foto) => {
  if (!foto) {
    return 'https://picsum.photos/id/99/800/600'; // Default foto jika tidak ada
  }
  if (foto.startsWith('http')) {
    return foto;
  }
  return `http://localhost:8000${foto}`;
}

const openModal = (mode, kegiatan = null) => {
  modalMode.value = mode
  errorMessage.value = ''
  if (mode === 'create') {
    formData.value = { id: null, nama: '', deskripsi: '', foto: null, foto_url: null }
  } else {
    formData.value = { ...kegiatan, foto: null }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  errorMessage.value = ''
}

// --- API METHODS ---
const fetchKegiatan = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await auth.api.get('/documentations')
    kegiatanList.value = response.data.data
  } catch (err) {
    console.error('Error fetching documentations:', err)
    errorMessage.value = err.response?.data?.message || 'Gagal memuat data. Periksa izin Anda.'
  } finally {
    isLoading.value = false
  }
}

const handleSave = async () => {
  isSaving.value = true
  errorMessage.value = ''
  
  try {
    const payload = new FormData();
    payload.append('nama', formData.value.nama);
    payload.append('deskripsi', formData.value.deskripsi);
    if (formData.value.foto) {
      payload.append('foto', formData.value.foto);
    }

    let response
    if (modalMode.value === 'create') {
      response = await auth.api.post('/documentations', payload, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      kegiatanList.value.push(response.data.data)
      showNotification('Kegiatan berhasil ditambahkan!', 'success')
    } else if (modalMode.value === 'edit') {
      const dataToUpdate = new FormData();
      dataToUpdate.append('_method', 'PUT');
      dataToUpdate.append('nama', formData.value.nama);
      dataToUpdate.append('deskripsi', formData.value.deskripsi);
      if (formData.value.foto instanceof File) {
        dataToUpdate.append('foto', formData.value.foto);
      }

      response = await auth.api.post(`/documentations/${formData.value.id}`, dataToUpdate, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const index = kegiatanList.value.findIndex(k => k.id === formData.value.id)
      if (index !== -1) {
        kegiatanList.value[index] = response.data.data
      }
      showNotification('Kegiatan berhasil diperbarui!', 'success')
    }
    
    closeModal()
  } catch (error) {
    console.error('Error saving documentation:', error)
    if (error.response?.status === 422 && error.response.data.errors) {
      validationErrors.value = error.response.data.errors
    } else {
      showNotification(error.response?.data?.message || 'Gagal menyimpan kegiatan. Periksa izin Anda.', 'error')
    }
  } finally {
    isSaving.value = false
  }
}

const handleDelete = async (kegiatan) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${kegiatan.nama}"?`)) {
    return
  }
  isDeleting.value = true
  try {
    await auth.api.delete(`/documentations/${kegiatan.id}`)
    const index = kegiatanList.value.findIndex(k => k.id === kegiatan.id)
    if (index !== -1) {
      kegiatanList.value.splice(index, 1)
    }
    showNotification('Kegiatan berhasil dihapus.', 'success')
  } catch (err) {
    console.error('Error deleting documentation:', err)
    showNotification(err.response?.data?.message || 'Gagal menghapus kegiatan. Periksa izin Anda.', 'error')
  } finally {
    isDeleting.value = false
  }
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    formData.value.foto = file
    const reader = new FileReader()
    reader.onload = (e) => {
      formData.value.foto_url = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// --- LIFECYCLE ---
onMounted(() => {
  fetchKegiatan()
})
</script>

<style scoped>
/* --- General Layout --- */
.kelola-kegiatan-container {
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

/* --- Notification --- */
.notification {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s ease-out;
}

.notification.success {
  background-color: #e8f5e9;
  color: #2e7d32;
  border-left: 5px solid #4caf50;
}

.notification.error {
  background-color: #fde8e8;
  color: #c62828;
  border-left: 5px solid #f44336;
}

.notification-close {
  background: none;
  border: none;
  margin-left: auto;
  cursor: pointer;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.2s;
}
.notification-close:hover { opacity: 1; }

@keyframes slideIn {
  from { transform: translateY(-20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
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

/* --- Search Bar --- */
.search-bar {
  position: relative;
  margin-bottom: 1.5rem;
  max-width: 400px;
}
.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}
.search-bar input:focus { outline: none; border-color: #007bce; }
.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
}

/* --- Table --- */
.table-wrapper {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th {
  background-color: #f8f9fa;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}
.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
}
.text-center {
  text-align: center;
}
.kegiatan-photo {
  width: 100px;
  height: 70px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid #e9ecef;
}
.action-buttons {
  display: flex;
  gap: 0.5rem;
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

.btn-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  font-size: 1rem;
}
.btn-icon:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-view { 
  background-color: #2196F3; 
  color: white; 
}
.btn-view:hover:not(:disabled) { 
  background-color: #0d8aee; 
  transform: scale(1.1); 
}
.btn-edit { 
  background-color: #FF9800; 
  color: white; 
}
.btn-edit:hover:not(:disabled) { 
  background-color: #e68900; 
  transform: scale(1.1); 
}
.btn-delete { 
  background-color: #F44336; 
  color: white; 
}
.btn-delete:hover:not(:disabled) { 
  background-color: #d32f2f; 
  transform: scale(1.1); 
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
  max-width: 500px;
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
.current-photo {
  width: 100%;
  max-width: 200px;
  height: auto;
  border-radius: 8px;
  margin-top: 0.5rem;
  border: 1px solid #ddd;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>