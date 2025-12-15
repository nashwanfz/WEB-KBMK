<script setup>
import { ref, computed, onMounted, inject } from 'vue'

// --- INJECT DATA DARI APP.VUE ---
const auth = inject('auth');

// --- STATE ---
const kegiatanList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create')
const formData = ref({
  id: null,
  nama: '',
  tanggal: '',
  deskripsi: ''
})
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)

// State untuk notifikasi
const notification = ref({ show: false, message: '', type: 'success', icon: 'fas fa-check-circle' })

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

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const openModal = (mode, kegiatan = null) => {
  modalMode.value = mode
  if (mode === 'create') {
    formData.value = { id: null, nama: '', tanggal: '', deskripsi: '' }
  } else {
    formData.value = { ...kegiatan }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// --- API METHODS ---
const fetchKegiatan = async () => {
  // PERBAIKAN: Tambahkan pemeriksaan keamanan
  if (!auth || !auth.value || !auth.value.api) {
    console.error("Auth atau API tidak tersedia saat fetchKegiatan");
    return;
  }

  isLoading.value = true
  try {
    const response = await auth.value.api.get('/schedules')
    kegiatanList.value = response.data.data
  } catch (err) {
    console.error('Error fetching schedules:', err)
    showNotification(err.response?.data?.message || 'Gagal memuat data. Periksa izin Anda.', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleSave = async () => {
  isSaving.value = true
  try {
    const payload = {
      nama: formData.value.nama,
      tanggal: formData.value.tanggal,
      deskripsi: formData.value.deskripsi
    }

    let response
    if (modalMode.value === 'create') {
      response = await auth.value.api.post('/schedules', payload)
      kegiatanList.value.push(response.data.data)
      showNotification('Jadwal kegiatan berhasil ditambahkan!', 'success')
    } else if (modalMode.value === 'edit') {
      response = await auth.value.api.put(`/schedules/${formData.value.id}`, payload)
      const index = kegiatanList.value.findIndex(k => k.id === formData.value.id)
      if (index !== -1) {
        kegiatanList.value[index] = response.data.data
      }
      showNotification('Jadwal kegiatan berhasil diperbarui!', 'success')
    }
    closeModal()
  } catch (err) {
    console.error('Error saving schedule:', err)
    showNotification(err.response?.data?.message || 'Gagal menyimpan jadwal. Periksa izin Anda.', 'error')
  } finally {
    isSaving.value = false
  }
}

const handleDelete = async (kegiatan) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus jadwal "${kegiatan.nama}"?`)) {
    return
  }
  isDeleting.value = true
  try {
    await auth.value.api.delete(`/schedules/${kegiatan.id}`)
    const index = kegiatanList.value.findIndex(k => k.id === kegiatan.id)
    if (index !== -1) {
      kegiatanList.value.splice(index, 1)
    }
    showNotification('Jadwal kegiatan berhasil dihapus.', 'success')
  } catch (err) {
    console.error('Error deleting schedule:', err)
    showNotification(err.response?.data?.message || 'Gagal menghapus jadwal. Periksa izin Anda.', 'error')
  } finally {
    isDeleting.value = false
  }
}

// Load data saat komponen pertama kali dimuat
onMounted(() => {
  fetchKegiatan()
})
</script>

<!-- PERBAIKAN: Gunakan pemeriksaan berlapis di template -->
<!-- PERBAIKAN: Gunakan optional chaining untuk pemeriksaan yang lebih andal -->
<template>
  <div class="kelola-kalender-container">
    <div v-if="notification.show" :class="['notification', notification.type]">
      <i :class="notification.icon"></i>
      <span>{{ notification.message }}</span>
      <button class="notification-close" @click="hideNotification">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="page-header">
      <h1>Kelola Kalender Kegiatan</h1>
      <!-- PERBAIKAN: Gunakan optional chaining ?. -->
      <button v-if="auth.value?.user?.role === 'superadmin'" class="btn btn-primary" @click="openModal('create')" :disabled="isLoading">
        <i class="fas fa-plus"></i> Tambah Jadwal Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama kegiatan atau deskripsi..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <div v-else class="table-wrapper">
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
          <tr v-if="filteredKegiatan.length === 0">
            <td colspan="4" class="text-center">Tidak ada jadwal kegiatan.</td>
          </tr>
          <tr v-for="kegiatan in filteredKegiatan" :key="kegiatan.id">
            <td>{{ kegiatan.nama }}</td>
            <td>{{ formatDate(kegiatan.tanggal) }}</td>
            <td>{{ kegiatan.deskripsi }}</td>
            <td class="action-buttons">
              <!-- PERBAIKAN: Gunakan optional chaining ?. -->
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-view" @click="openModal('view', kegiatan)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-edit" @click="openModal('edit', kegiatan)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-delete" @click="handleDelete(kegiatan)" title="Hapus" :disabled="isDeleting">
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
              <label for="tanggal">Tanggal</label>
              <input type="date" id="tanggal" v-model="formData.tanggal" :disabled="modalMode === 'view' || isSaving" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view' || isSaving" rows="4" required></textarea>
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

<style scoped>
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

/* --- Loading Indicator --- */
.loading-indicator {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #007bce;
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
.form-group input[type="date"],
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
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>