<script setup>
import { ref, computed, onMounted, inject } from 'vue'

// --- INTEGRASI API ---
// Inject data autentikasi dari App.vue
const auth = inject('auth');

// --- STATE ---
const gformList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create')
const formData = ref({ id: null, nama: '', link: '' })
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const error = ref(null)
const validationErrors = ref({}) 

// State untuk notifikasi
const notification = ref({ show: false, message: '', type: 'success', icon: 'fas fa-check-circle' })

// --- COMPUTED ---
const filteredGform = computed(() => {
  if (!searchQuery.value) return gformList.value
  const query = searchQuery.value.toLowerCase()
  return gformList.value.filter(gform => gform.nama.toLowerCase().includes(query))
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Link GForm Baru'
    case 'view': return 'Detail Link GForm'
    case 'edit': return 'Edit Link GForm'
    default: return 'Form Link GForm'
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

const openModal = (mode, gform = null) => {
  modalMode.value = mode
  validationErrors.value = {}
  
  if (mode === 'create') {
    formData.value = { id: null, nama: '', link: '' }
  } else {
    formData.value = { ...gform }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  validationErrors.value = {}
}

// --- API METHODS (MENGGUNAKAN auth.api) ---
const fetchLinks = async () => {
  isLoading.value = true
  error.value = null
  try {
    const response = await auth.api.get('/links')
    gformList.value = response.data.data
  } catch (err) {
    console.error('Error fetching links:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data. Periksa izin Anda.'
  } finally {
    isLoading.value = false
  }
}

const createLink = async (linkData) => {
  isSaving.value = true
  validationErrors.value = {}
  try {
    const response = await auth.api.post('/links', linkData)
    gformList.value.push(response.data.data)
    closeModal()
    showNotification('Link GForm berhasil ditambahkan!', 'success')
    return true
  } catch (err) {
    console.error('Error creating link:', err)
    if (err.response?.status === 422 && err.response.data.errors) {
      validationErrors.value = err.response.data.errors
    } else {
      showNotification(err.response?.data?.message || 'Gagal menambahkan link. Periksa izin Anda.', 'error')
    }
    return false
  } finally {
    isSaving.value = false
  }
}

const updateLink = async (id, linkData) => {
  isSaving.value = true
  validationErrors.value = {}
  try {
    const response = await auth.api.put(`/links/${id}`, linkData)
    const index = gformList.value.findIndex(g => g.id === id)
    if (index !== -1) {
      gformList.value[index] = response.data.data
    }
    closeModal()
    showNotification('Link GForm berhasil diperbarui!', 'success')
    return true
  } catch (err) {
    console.error('Error updating link:', err)
    if (err.response?.status === 422 && err.response.data.errors) {
      validationErrors.value = err.response.data.errors
    } else {
      showNotification(err.response?.data?.message || 'Gagal memperbarui link. Periksa izin Anda.', 'error')
    }
    return false
  } finally {
    isSaving.value = false
  }
}

const deleteLink = async (id) => {
  try {
    await auth.api.delete(`/links/${id}`)
    const index = gformList.value.findIndex(g => g.id === id)
    if (index !== -1) {
      gformList.value.splice(index, 1)
    }
    showNotification('Link GForm berhasil dihapus.', 'success')
  } catch (err) {
    console.error('Error deleting link:', err)
    showNotification(err.response?.data?.message || 'Gagal menghapus link. Periksa izin Anda.', 'error')
  }
}

const handleDelete = async (gform) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${gform.nama}"?`)) {
    return
  }
  isDeleting.value = true
  try {
    await deleteLink(gform.id)
  } finally {
    isDeleting.value = false
  }
}

const handleSave = async () => {
  if (modalMode.value === 'create') {
    await createLink({ nama: formData.value.nama, link: formData.value.link })
  } else if (modalMode.value === 'edit') {
    await updateLink(formData.value.id, { nama: formData.value.nama, link: formData.value.link })
  }
}

// --- LIFECYCLE ---
onMounted(() => {
  fetchLinks()
})
</script>

<template>
  <div class="kelola-gform-container">
    <!-- Notifikasi Sederhana -->
    <div v-if="notification.show" :class="['notification', notification.type]">
      <i :class="notification.icon"></i>
      <span>{{ notification.message }}</span>
      <button class="notification-close" @click="hideNotification">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="page-header">
      <h1>Kelola Link GForm</h1>
      <!-- Tombol Tambah hanya muncul untuk Admin & Superadmin -->
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openModal('create')" :disabled="isLoading">
        <i class="fas fa-plus"></i> Tambah Link Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama form..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <div v-else-if="error" class="error-message">
      <i class="fas fa-exclamation-circle"></i> {{ error }}
      <button class="btn btn-primary" @click="fetchLinks">Coba Lagi</button>
    </div>

    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Form</th>
            <th>Link GForm</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="gform in filteredGform" :key="gform.id">
            <td>{{ gform.nama }}</td>
            <td>
              <a :href="gform.link" target="_blank" class="gform-link">
                {{ gform.link }}
              </a>
            </td>
            <td class="action-buttons">
              <!-- PERUBAHAN KRUSIAL: Tombol Edit & Hapus hanya untuk Admin -->
              <button v-if="auth.isAdmin" class="btn-icon btn-edit" @click="openModal('edit', gform)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button v-if="auth.isAdmin" class="btn-icon btn-delete" @click="handleDelete(gform)" title="Hapus" :disabled="isDeleting">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="filteredGform.length === 0" class="no-data">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data yang ditemukan</p>
      </div>
    </div>

    <!-- Modal -->
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
              <label for="namaForm">Nama Form</label>
              <input 
                type="text" 
                id="namaForm" 
                v-model="formData.nama" 
                :disabled="modalMode === 'view' || isSaving" 
                :class="{ 'is-invalid': validationErrors.nama }"
                required 
              />
              <div v-if="validationErrors.nama" class="error-text">
                {{ validationErrors.nama[0] }}
              </div>
            </div>
            <div class="form-group">
              <label for="linkGform">Link GForm</label>
              <input 
                type="url" 
                id="linkGform" 
                v-model="formData.link" 
                placeholder="https://forms.gle/..." 
                :disabled="modalMode === 'view' || isSaving"
                :class="{ 'is-invalid': validationErrors.link }"
                required 
              />
              <div v-if="validationErrors.link" class="error-text">
                {{ validationErrors.link[0] }}
              </div>
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
/* ... Salin semua style CSS yang Anda berikan di sini ... */
.kelola-gform-container {
  padding: 1.5rem;
  background-color: #f4f6f9;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
.loading-indicator, .error-message, .no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
}

.loading-indicator i, .error-message i, .no-data i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.loading-indicator i { color: #007bce; }
.error-message i { color: #F44336; }
.no-data i { color: #6c757d; }
.error-message { color: #F44336; }

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
.gform-link {
  color: #007bce;
  text-decoration: none;
  word-break: break-all;
  transition: color 0.2s;
}
.gform-link:hover { color: #0056b3; text-decoration: underline; }
.action-buttons { display: flex; gap: 0.5rem; }

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
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { background-color: #007bce; color: white; }
.btn-primary:hover:not(:disabled) { background-color: #0056b3; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover:not(:disabled) { background-color: #5a6268; }

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
.btn-edit { background-color: #FF9800; color: white; }
.btn-edit:hover:not(:disabled) { background-color: #e68900; transform: scale(1.1); }
.btn-delete { background-color: #F44336; color: white; }
.btn-delete:hover:not(:disabled) { background-color: #d32f2f; transform: scale(1.1); }

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
.modal-header h3 { margin: 0; color: #2c3e50; }
.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #aaa;
}
.modal-body { padding: 1.5rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
}
.form-group input[type="text"],
.form-group input[type="url"] {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus { outline: none; border-color: #007bce; box-shadow: 0 0 0 3px rgba(0, 123, 206, 0.25); }
.form-group input:disabled { background-color: #f8f9fa; cursor: not-allowed; }
.form-group input.is-invalid { border-color: #dc3545; }
.error-text {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
.error-text::before {
  content: '!';
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  background-color: #dc3545;
  color: white;
  border-radius: 50%;
  font-size: 0.7rem;
  font-weight: bold;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>