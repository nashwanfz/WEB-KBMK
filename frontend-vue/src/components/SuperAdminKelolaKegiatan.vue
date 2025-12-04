<template>
  <div class="kelola-kegiatan-container">
    <div class="page-header">
      <h1>Kelola Kegiatan</h1>
      <button class="btn btn-primary" @click="openModal('create')">
        <i class="fas fa-plus"></i> Tambah Kegiatan Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama kegiatan..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <!-- PERUBAHAN 1: Ganti struktur loading dan tabel -->
    <!-- Tampilkan indikator loading di luar tabel -->
    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <!-- Tampilkan tabel jika TIDAK loading -->
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
          <!-- PERUBAHAN 2: Hapus baris loading lama dari dalam tbody -->
          <!-- Tampilkan pesan jika tidak ada data -->
          <tr v-if="filteredKegiatan.length === 0">
            <td colspan="4" class="text-center">Tidak ada kegiatan.</td>
          </tr>
          <!-- Tampilkan data kegiatan -->
          <tr v-for="kegiatan in filteredKegiatan" :key="kegiatan.id">
            <td>
              <img :src="kegiatan.foto_url || getDefaultPhoto()" alt="Foto Kegiatan" class="kegiatan-photo" />
            </td>
            <td>{{ kegiatan.nama }}</td>
            <td>{{ kegiatan.deskripsi }}</td>
            <td class="action-buttons">
              <button class="btn-icon btn-view" @click="openModal('view', kegiatan)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn-icon btn-edit" @click="openModal('edit', kegiatan)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn-icon btn-delete" @click="handleDelete(kegiatan)" title="Hapus">
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
              <input type="text" id="nama" v-model="formData.nama" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view'" required rows="4"></textarea>
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="foto">Foto Kegiatan</label>
              <input type="file" id="foto" @change="handlePhotoUpload" accept="image/*" />
              <p class="photo-hint">*Upload foto untuk mengganti. Kosongkan jika tidak ingin mengubah.</p>
            </div>
            <div class="form-group" v-if="modalMode === 'view' && formData.foto_url">
              <label>Foto Saat Ini</label>
              <img :src="formData.foto_url" alt="Foto Kegiatan" class="current-photo" />
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
              <span v-else>Simpan</span>
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// ... (Bagian script tidak berubah, sudah benar)
import { ref, computed, onMounted, inject } from 'vue'
import axios from 'axios'

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
const errorMessage = ref('')

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

const getDefaultPhoto = () => {
  return 'https://picsum.photos/id/99/800/600'
}

const fetchKegiatan = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await api.get('/documentations')
    kegiatanList.value = response.data.data
  } catch (error) {
    console.error('Error fetching documentations:', error)
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

const handleSave = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const dataPayload = new FormData();
    dataPayload.append('nama', formData.value.nama);
    dataPayload.append('deskripsi', formData.value.deskripsi);
    
    if (formData.value.foto) {
      dataPayload.append('foto', formData.value.foto);
    }

    let response
    if (modalMode.value === 'create') {
      response = await api.post('/documentations', dataPayload)
      kegiatanList.value.push(response.data.data)
      alert(response.data.message)
    } else if (modalMode.value === 'edit') {
      dataPayload.append('_method', 'PUT');
      response = await api.post(`/documentations/${formData.value.id}`, dataPayload)
      
      const index = kegiatanList.value.findIndex(k => k.id === formData.value.id)
      if (index !== -1) {
        kegiatanList.value[index] = response.data.data
      }
      alert(response.data.message)
    }
    closeModal()
  } catch (error) {
    console.error('Error saving documentation:', error)
    if (error.response && error.response.status === 422) {
      const errors = error.response.data
      let errorMsg = 'Terjadi kesalahan validasi:\n'
      for (const field in errors) {
        errorMsg += `${errors[field].join(', ')}\n`
      }
      errorMessage.value = errorMsg
    } else {
      errorMessage.value = 'Gagal menyimpan kegiatan. Periksa konsol untuk detail.'
    }
  } finally {
    isLoading.value = false
  }
}

const handleDelete = async (kegiatan) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${kegiatan.nama}"?`)) {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
      const response = await api.delete(`/documentations/${kegiatan.id}`)
      const index = kegiatanList.value.findIndex(k => k.id === kegiatan.id)
      if (index !== -1) {
        kegiatanList.value.splice(index, 1)
      }
      alert(response.data.message)
    } catch (error) {
      console.error('Error deleting documentation:', error)
      errorMessage.value = 'Gagal menghapus kegiatan. Periksa konsol untuk detail.'
    } finally {
      isLoading.value = false
    }
  }
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    formData.value.foto = file
  }
}

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

/* --- PERUBAHAN 3: Tambahkan style untuk loading indicator --- */
.loading-indicator {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #007bce; /* Warna biru sesuai permintaan */
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
}

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

/* Style khusus untuk foto kegiatan (persegi panjang) */
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
.btn-secondary:hover {
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

.btn-view { 
  background-color: #2196F3; 
  color: white; 
}
.btn-view:hover { 
  background-color: #0d8aee; 
  transform: scale(1.1); 
}

.btn-edit { 
  background-color: #FF9800; 
  color: white; 
}
.btn-edit:hover { 
  background-color: #e68900; 
  transform: scale(1.1); 
}

.btn-delete { 
  background-color: #F44336; 
  color: white; 
}
.btn-delete:hover { 
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
.form-group input[type="file"],
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box;
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