<template>
  <div class="kelola-kalender-container">
    <div class="page-header">
      <h1>Kelola Kalender Kegiatan</h1>
      <button class="btn btn-primary" @click="openModal('create')">
        <i class="fas fa-plus"></i> Tambah Jadwal Baru
      </button>
    </div>

    <!-- Tampilkan pesan error jika ada -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama kegiatan atau deskripsi..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Kegiatan</th>
            <th>Tanggal</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <!-- Tampilkan loading jika sedang mengambil data -->
          <tr v-if="isLoading">
            <td colspan="4" class="text-center">Memuat data...</td>
          </tr>
          <!-- Tampilkan pesan jika tidak ada data -->
          <tr v-else-if="!isLoading && filteredKegiatan.length === 0">
            <td colspan="4" class="text-center">Tidak ada jadwal kegiatan.</td>
          </tr>
          <!-- Tampilkan data jadwal -->
          <tr v-for="kegiatan in filteredKegiatan" :key="kegiatan.id">
            <td>{{ kegiatan.nama }}</td>
            <td>{{ formatDate(kegiatan.tanggal) }}</td>
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
              <label for="tanggal">Tanggal</label>
              <input type="date" id="tanggal" v-model="formData.tanggal" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view'" rows="4" required></textarea>
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// Konfigurasi API
// Ganti dengan URL backend Anda jika perlu
const API_URL = '/api' 

// Buat instance axios dengan konfigurasi default
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    // Ambil token dari localStorage dan tambahkan ke header
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  }
})

// --- STATE ---
const kegiatanList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create') // 'create', 'view', 'edit'
const formData = ref({
  id: null,
  nama: '',
  tanggal: '',
  deskripsi: ''
})
const isLoading = ref(false)
const errorMessage = ref('')

// --- COMPUTED ---
const filteredKegiatan = computed(() => {
  if (!searchQuery.value) {
    return kegiatanList.value
  }
  const query = searchQuery.value.toLowerCase()
  return kegiatanList.value.filter(kegiatan =>
    kegiatan.nama.toLowerCase().includes(query) ||
    kegiatan.deskripsi.toLowerCase().includes(query)
  )
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Jadwal Kegiatan Baru'
    case 'view': return 'Detail Jadwal Kegiatan'
    case 'edit': return 'Edit Jadwal Kegiatan'
    default: return 'Form Kegiatan'
  }
})

// --- METHODS ---
const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Fetch data dari API endpoint /schedules
const fetchKegiatan = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const response = await api.get('/schedules')
    // Sesuaikan dengan struktur respons API { "data": [...] }
    kegiatanList.value = response.data.data
  } catch (error) {
    console.error('Error fetching schedules:', error)
    errorMessage.value = 'Gagal memuat data jadwal. Silakan coba lagi.'
    
    // Jika error terkait autentikasi (401), redirect ke halaman login
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token')
      // Asumsikan Anda memiliki router Vue
      // router.push('/login') 
      window.location.href = '/login' // Fallback
    }
  } finally {
    isLoading.value = false
  }
}

const openModal = (mode, kegiatan = null) => {
  modalMode.value = mode
  errorMessage.value = '' // Reset error saat modal dibuka
  if (mode === 'create') {
    formData.value = { id: null, nama: '', tanggal: '', deskripsi: '' }
  } else {
    // Salin data untuk menghindari modifikasi langsung pada data asli
    formData.value = { ...kegiatan }
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
    let response
    const payload = {
      nama: formData.value.nama,
      tanggal: formData.value.tanggal,
      deskripsi: formData.value.deskripsi
    }

    if (modalMode.value === 'create') {
      response = await api.post('/schedules', payload)
      // Tambahkan data baru ke list
      kegiatanList.value.push(response.data.data)
      alert(response.data.message) // Tampilkan pesan sukses dari API
    } else if (modalMode.value === 'edit') {
      response = await api.put(`/schedules/${formData.value.id}`, payload)
      // Update data yang ada di list
      const index = kegiatanList.value.findIndex(k => k.id === formData.value.id)
      if (index !== -1) {
        kegiatanList.value[index] = response.data.data
      }
      alert(response.data.message) // Tampilkan pesan sukses dari API
    }
    closeModal()
  } catch (error) {
    console.error('Error saving schedule:', error)
    
    if (error.response && error.response.status === 422) {
      // Error validasi dari Laravel
      const errors = error.response.data
      let errorMsg = 'Terjadi kesalahan validasi:\n'
      
      // Iterasi error untuk setiap field
      for (const field in errors) {
        // Laravel mengembalikan error sebagai array string
        if (Array.isArray(errors[field])) {
          errorMsg += `${errors[field].join(', ')}\n`
        } else {
          errorMsg += `${errors[field]}\n`
        }
      }
      errorMessage.value = errorMsg
    } else {
      errorMessage.value = 'Gagal menyimpan jadwal. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}

const handleDelete = async (kegiatan) => {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal "${kegiatan.nama}"?`)) {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
      const response = await api.delete(`/schedules/${kegiatan.id}`)
      
      // Hapus dari list tanpa perlu fetch ulang
      const index = kegiatanList.value.findIndex(k => k.id === kegiatan.id)
      if (index !== -1) {
        kegiatanList.value.splice(index, 1)
      }
      alert(response.data.message) // Tampilkan pesan sukses dari API
    } catch (error) {
      console.error('Error deleting schedule:', error)
      if (error.response && error.response.data && error.response.data.message) {
          errorMessage.value = error.response.data.message
      } else {
          errorMessage.value = 'Gagal menghapus jadwal. Silakan coba lagi.'
      }
    } finally {
      isLoading.value = false
    }
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchKegiatan()
})
</script>

<style scoped>
/* ... Gaya CSS tetap sama ... */
/* --- General Layout --- */
.kelola-kalender-container {
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

/* Alert Styling */
.alert {
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.25rem;
}
.alert-danger {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
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
.form-group input[type="date"],
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box; /* Penting agar padding tidak menambah lebar */
}
.form-group input:disabled,
.form-group textarea:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>