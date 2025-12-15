<script setup>
import { ref, computed, onMounted, inject } from 'vue'

// PERBAIKAN: Inject objek auth secara keseluruhan
const auth = inject('auth');

// --- STATE ---
const pengurusList = ref([])
const divisionsList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create')
const formData = ref({
  id: null,
  nama: '',
  jabatan: '',
  division_id: null,
  deskripsi: '',
  foto: null,
  foto_url: null
})
const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const notification = ref({ show: false, message: '', type: 'success', icon: 'fas fa-check-circle' })

// --- COMPUTED ---
const filteredAnggota = computed(() => {
  if (!searchQuery.value) {
    return pengurusList.value
  }
  const query = searchQuery.value.toLowerCase()
  return pengurusList.value.filter(anggota =>
    anggota.nama.toLowerCase().includes(query) ||
    anggota.jabatan.toLowerCase().includes(query)
  )
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Anggota Baru'
    case 'view': return 'Detail Anggota'
    case 'edit': return 'Edit Anggota'
    default: return 'Form Anggota'
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

const getDefaultPhoto = () => 'https://i.pravatar.cc/150?d=mp'

const getPhotoUrl = (foto) => {
  if (!foto) return getDefaultPhoto()
  if (foto.startsWith('http') || foto.startsWith('data:')) return foto
  return `https://kbmk.unmul.ac.id/api/storage/${foto}`
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

const getDivisionName = (anggota) => {
  if (anggota.division && anggota.division.nama) {
    return anggota.division.nama
  }
  return '-'
}

const openModal = (mode, anggota = null) => {
  modalMode.value = mode
  if (mode === 'create') {
    formData.value = { 
      id: null, 
      nama: '', 
      jabatan: '', 
      division_id: null, 
      deskripsi: '', 
      foto: null, 
      foto_url: null 
    }
  } else {
    formData.value = { 
      ...anggota, 
      division_id: anggota.division_id || null,
      foto: null,
      foto_url: anggota.foto || anggota.foto_url 
    }
    console.log('📝 Form Data:', formData.value)
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// --- API METHODS ---
const fetchDivisions = async () => {
  // PERBAIKAN: Tambahkan pemeriksaan keamanan
  if (!auth || !auth.value || !auth.value.api) {
    console.error("Auth atau API tidak tersedia saat fetchDivisions");
    return;
  }

  try {
    const response = await auth.value.api.get('/divisions')
    divisionsList.value = response.data.data
    console.log('✅ Divisions loaded:', divisionsList.value)
  } catch (err) {
    console.error('❌ Error loading divisions:', err)
    showNotification('Gagal memuat daftar divisi.', 'error')
  }
}

const fetchPengurus = async () => {
  // PERBAIKAN: Tambahkan pemeriksaan keamanan
  if (!auth || !auth.value || !auth.value.api) {
    console.error("Auth atau API tidak tersedia saat fetchPengurus");
    return;
  }

  isLoading.value = true
  try {
    const response = await auth.value.api.get('/pengurus')
    pengurusList.value = response.data.data
    console.log('✅ Pengurus loaded:', pengurusList.value)
  } catch (err) {
    console.error('❌ Error loading pengurus:', err)
    showNotification(err.response?.data?.message || 'Gagal memuat data anggota.', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleSave = async () => {
  if (!formData.value.nama || !formData.value.division_id || !formData.value.deskripsi) {
    showNotification('Nama, Divisi, dan Deskripsi wajib diisi!', 'error')
    return
  }

  isSaving.value = true
  try {
    const dataToSend = new FormData()
    dataToSend.append('nama', formData.value.nama)
    dataToSend.append('jabatan', formData.value.jabatan || '')
    dataToSend.append('division_id', formData.value.division_id)
    dataToSend.append('deskripsi', formData.value.deskripsi)
    
    if (formData.value.foto instanceof File) {
      dataToSend.append('foto', formData.value.foto)
    }

    let response
    if (modalMode.value === 'create') {
      response = await auth.value.api.post('/pengurus', dataToSend, { 
        headers: { 'Content-Type': 'multipart/form-data' } 
      })
      showNotification('Anggota berhasil ditambahkan!', 'success')
    } else if (modalMode.value === 'edit') {
      dataToSend.append('_method', 'PUT')
      response = await auth.value.api.post(`/pengurus/${formData.value.id}`, dataToSend, { 
        headers: { 'Content-Type': 'multipart/form-data' } 
      })
      showNotification('Data anggota berhasil diperbarui!', 'success')
    }

    console.log('✅ Save response:', response.data)
    
    await fetchPengurus()
    closeModal()
  } catch (error) {
    console.error('❌ Save error:', error)
    console.error('Error response:', error.response?.data)
    showNotification(
      error.response?.data?.message || 'Gagal menyimpan data anggota.', 
      'error'
    )
  } finally {
    isSaving.value = false
  }
}

const handleDelete = async (anggota) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${anggota.nama}"?`)) {
    return
  }
  isDeleting.value = true
  try {
    await auth.value.api.delete(`/pengurus/${anggota.id}`)
    showNotification('Anggota berhasil dihapus.', 'success')
    await fetchPengurus()
  } catch (error) {
    console.error('❌ Delete error:', error)
    showNotification(
      error.response?.data?.message || 'Gagal menghapus data anggota.', 
      'error'
    )
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

onMounted(() => {
  fetchDivisions()
  fetchPengurus()
})
</script>

<!-- PERBAIKAN: Gunakan pemeriksaan berlapis di template -->
<!-- PERBAIKAN: Gunakan optional chaining untuk pemeriksaan yang lebih andal -->
<template>
  <div class="kelola-anggota-container">
    <div v-if="notification.show" :class="['notification', notification.type]">
      <i :class="notification.icon"></i>
      <span>{{ notification.message }}</span>
      <button class="notification-close" @click="hideNotification">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="page-header">
      <h1>Kelola Anggota</h1>
      <!-- PERBAIKAN: Gunakan optional chaining ?. -->
      <button v-if="auth.value?.user?.role === 'superadmin'" class="btn btn-primary" @click="openModal('create')" :disabled="isLoading">
        <i class="fas fa-plus"></i> Tambah Anggota Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama atau jabatan..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </div>

    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nama Anggota</th>
            <th>Jabatan</th>
            <th>Divisi</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="anggota in filteredAnggota" :key="anggota.id">
            <td>
              <img :src="getPhotoUrl(anggota.foto)" alt="Foto" class="anggota-photo" />
            </td>
            <td>{{ anggota.nama }}</td>
            <td>{{ anggota.jabatan || '-' }}</td>
            <td>{{ getDivisionName(anggota) }}</td>
            <td>{{ formatDate(anggota.created_at) }}</td>
            <td class="action-buttons">
              <!-- PERBAIKAN: Gunakan optional chaining ?. -->
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-view" @click="openModal('view', anggota)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-edit" @click="openModal('edit', anggota)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button v-if="auth.value?.user?.role === 'superadmin'" class="btn-icon btn-delete" @click="handleDelete(anggota)" title="Hapus" :disabled="isDeleting">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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
              <label for="nama">Nama Anggota <span class="required">*</span></label>
              <input type="text" id="nama" v-model="formData.nama" :disabled="modalMode === 'view' || isSaving" required />
            </div>
            <div class="form-group">
              <label for="jabatan">Jabatan</label>
              <input type="text" id="jabatan" v-model="formData.jabatan" :disabled="modalMode === 'view' || isSaving" placeholder="Contoh: Ketua Umum" />
            </div>
            <div class="form-group">
              <label for="division_id">Divisi <span class="required">*</span></label>
              <select id="division_id" v-model="formData.division_id" :disabled="modalMode === 'view' || isSaving" required>
                <option value="" disabled>-- Pilih Divisi --</option>
                <option v-for="division in divisionsList" :key="division.id" :value="division.id">
                  {{ division.nama }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi <span class="required">*</span></label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view' || isSaving" rows="3" required></textarea>
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="foto">Foto</label>
              <input type="file" id="foto" @change="handlePhotoUpload" accept="image/*" :disabled="isSaving" />
              <p class="photo-hint">*Format: JPG, PNG, GIF. Maksimal 2MB.</p>
            </div>
            <div class="form-group" v-if="formData.foto_url">
              <label>{{ modalMode === 'view' ? 'Foto Saat Ini' : 'Preview Foto' }}</label>
              <img :src="getPhotoUrl(formData.foto_url)" alt="Foto Anggota" class="current-photo" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isSaving">Batal</button>
            <button type="button" class="btn btn-primary" @click="handleSave" :disabled="isSaving">
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
.kelola-anggota-container {
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

.loading-indicator {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #007bce;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

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

.anggota-photo {
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
}

.btn-icon:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-view { background-color: #2196F3; color: white; }
.btn-view:hover:not(:disabled) { background-color: #0d8aee; transform: scale(1.1); }

.btn-edit { background-color: #FF9800; color: white; }
.btn-edit:hover:not(:disabled) { background-color: #e68900; transform: scale(1.1); }

.btn-delete { background-color: #F44336; color: white; }
.btn-delete:hover:not(:disabled) { background-color: #d32f2f; transform: scale(1.1); }

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

.required {
  color: #f44336;
}

.form-group input[type="text"],
.form-group select,
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
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #007bce;
  box-shadow: 0 0 0 3px rgba(0, 123, 206, 0.25);
}

.form-group input:disabled,
.form-group select:disabled,
.form-group textarea:disabled {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.photo-hint {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.25rem;
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