<template>
  <div class="kelola-surat-container">
    <div class="page-header">
      <h1>Kelola Surat Masuk</h1>
      <button class="btn btn-primary" @click="openModal('view')">
        <i class="fas fa-plus"></i> Lihat Detail Surat
      </button>
    </div>

    <!-- Kartu Ringkasan Total Surat -->
    <div class="summary-cards">
      <div class="summary-card">
        <i class="fas fa-file-alt"></i>
        <div class="summary-info">
          <h3>Total Surat</h3>
          <p>{{ totalSurat }}</p>
        </div>
      </div>
      <div class="summary-card pending">
        <i class="fas fa-clock"></i>
        <div class="summary-info">
          <h3>Menunggu Proses</h3>
          <p>{{ totalPending }}</p>
        </div>
      </div>
      <div class="summary-card processing">
        <i class="fas fa-spinner"></i>
        <div class="summary-info">
          <h3>Sedang Diproses</h3>
          <p>{{ totalProcessing }}</p>
        </div>
      </div>
      <div class="summary-card completed">
        <i class="fas fa-check-circle"></i>
        <div class="summary-info">
          <h3>Selesai</h3>
          <p>{{ totalCompleted }}</p>
        </div>
      </div>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama pengirim, perihal, tujuan, atau instansi..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div class="filter-bar">
      <select v-model="statusFilter">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu Proses</option>
        <option value="diteruskan">Diteruskan</option>
        <option value="diproses">Diproses</option>
        <option value="selesai">Selesai</option>
      </select>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nomor Surat</th>
            <th>Nama Pengirim</th>
            <th>Perihal</th>
            <th>Tujuan</th>
            <th>Asal Instansi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="surat in filteredSurat" :key="surat.id">
            <td>{{ surat.nomor_surat }}</td>
            <td>{{ surat.nama_pengirim }}</td>
            <td>{{ surat.perihal }}</td>
            <td>{{ surat.tujuan }}</td>
            <td>{{ surat.asal_instansi || '-' }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(surat.status)]">
                {{ formatStatus(surat.status) }}
              </span>
            </td>
            <td class="action-buttons">
              <!-- Tombol Lihat Detail (Biru) -->
              <button class="btn-icon btn-view" @click="openModal('view', surat)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <!-- Tombol Download PDF (Hijau) -->
              <button class="btn-icon btn-download" @click="downloadFile(surat)" title="Download File">
                <i class="fas fa-download"></i>
              </button>
              <!-- Tombol Teruskan (Oranye) -->
              <button v-if="surat.status === 'pending'" class="btn-icon btn-forward" @click="openModal('assign', surat)" title="Teruskan ke Koordinator">
                <i class="fas fa-share"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="loading" class="loading-indicator">
        <i class="fas fa-spinner fa-spin"></i> Memuat data...
      </div>
      <div v-if="!loading && filteredSurat.length === 0" class="no-data">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data surat yang ditemukan</p>
      </div>
    </div>

    <!-- Modal untuk Lihat Detail dan Penugasan -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ modalTitle }}</h3>
          <button class="close-btn" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <!-- View Detail Mode -->
          <div v-if="modalMode === 'view'">
            <div class="detail-section">
              <h4>Informasi Surat</h4>
              <div class="detail-row">
                <span class="label">Nomor Surat:</span>
                <span class="value">{{ formData.nomor_surat }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Nama Pengirim:</span>
                <span class="value">{{ formData.nama_pengirim }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Email Pengirim:</span>
                <span class="value">{{ formData.email_pengirim || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Perihal:</span>
                <span class="value">{{ formData.perihal }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Tujuan:</span>
                <span class="value">{{ formData.tujuan }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Asal Instansi:</span>
                <span class="value">{{ formData.asal_instansi || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Jenis Surat:</span>
                <span class="value">{{ formData.jenis_surat }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Status:</span>
                <span :class="['status-badge', getStatusClass(formData.status)]">
                  {{ formatStatus(formData.status) }}
                </span>
              </div>
              <div class="detail-row">
                <span class="label">File Surat:</span>
                <a :href="getFileUrl(formData.file_surat)" target="_blank" class="file-link">
                  <i class="fas fa-file-pdf"></i> Lihat File
                </a>
              </div>
            </div>
            
            <div v-if="formData.dispositions && formData.dispositions.length > 0" class="detail-section">
              <h4>Riwayat Penugasan</h4>
              <div v-for="disposition in formData.dispositions" :key="disposition.id" class="disposition-item">
                <div class="disposition-header">
                  <span class="assigned-to">Ditugaskan kepada: {{ disposition.assignedUser ? disposition.assignedUser.username : 'Tidak Diketahui' }}</span>
                  <span :class="['status-badge', 'small', getStatusClass(disposition.status)]">
                    {{ formatStatus(disposition.status) }}
                  </span>
                </div>
                <div v-if="disposition.catatan" class="disposition-note">
                  Catatan: {{ disposition.catatan }}
                </div>
                <div class="disposition-date">
                  {{ formatDate(disposition.created_at) }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Assign Mode -->
          <div v-else-if="modalMode === 'assign'">
            <form @submit.prevent="handleAssign">
              <div class="form-group">
                <label for="assigned_to">Tugaskan kepada:</label>
                <select id="assigned_to" v-model="assignForm.assigned_to" required>
                  <option value="">-- Pilih Koordinator --</option>
                  <option v-for="user in coordinators" :key="user.id" :value="user.id">
                    {{ user.username }} ({{ user.divisi }})
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label for="catatan">Catatan:</label>
                <textarea id="catatan" v-model="assignForm.catatan" rows="3"></textarea>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else-if="modalMode === 'assign'">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleAssign">Teruskan</button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// --- STATE ---
const suratList = ref([])
const coordinators = ref([])
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const modalMode = ref('view') // 'view', 'assign'
const formData = ref({
  id: null,
  nomor_surat: '',
  nama_pengirim: '',
  email_pengirim: '',
  perihal: '',
  tujuan: '',
  asal_instansi: '',
  jenis_surat: '',
  status: '',
  file_surat: '',
  dispositions: []
})
const assignForm = ref({
  assigned_to: '',
  catatan: ''
})

// --- COMPUTED ---
const filteredSurat = computed(() => {
  let result = suratList.value
  
  // Filter berdasarkan status
  if (statusFilter.value) {
    result = result.filter(surat => surat.status === statusFilter.value)
  }
  
  // Filter berdasarkan query pencarian
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(surat =>
      surat.nama_pengirim.toLowerCase().includes(query) ||
      surat.perihal.toLowerCase().includes(query) ||
      surat.tujuan.toLowerCase().includes(query) ||
      (surat.asal_instansi && surat.asal_instansi.toLowerCase().includes(query))
    )
  }
  
  return result
})

const totalSurat = computed(() => {
  return suratList.value.length
})

const totalPending = computed(() => {
  return suratList.value.filter(surat => surat.status === 'pending').length
})

const totalProcessing = computed(() => {
  return suratList.value.filter(surat => ['diteruskan', 'diproses'].includes(surat.status)).length
})

const totalCompleted = computed(() => {
  return suratList.value.filter(surat => surat.status === 'selesai').length
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'view': return 'Detail Surat'
    case 'assign': return 'Teruskan Surat'
    default: return 'Form Surat'
  }
})

// --- METHODS ---
const fetchSurat = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/surat-requests')
    suratList.value = response.data.data
  } catch (error) {
    console.error('Error fetching surat:', error)
    alert('Gagal memuat data surat. Silakan coba lagi.')
  } finally {
    loading.value = false
  }
}

const fetchCoordinators = async () => {
  try {
    const response = await axios.get('/api/users/coordinators')
    coordinators.value = response.data.data
  } catch (error) {
    console.error('Error fetching coordinators:', error)
  }
}

const openModal = (mode, surat = null) => {
  modalMode.value = mode
  if (mode === 'view' && surat) {
    formData.value = { ...surat }
  } else if (mode === 'assign' && surat) {
    formData.value = { ...surat }
    assignForm.value = {
      assigned_to: '',
      catatan: ''
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleAssign = async () => {
  try {
    const response = await axios.patch(`/api/surat-requests/${formData.value.id}/assign`, assignForm.value)
    
    // Update data di local
    const index = suratList.value.findIndex(s => s.id === formData.value.id)
    if (index !== -1) {
      suratList.value[index] = response.data.data
    }
    
    alert('Surat berhasil diteruskan!')
    closeModal()
  } catch (error) {
    console.error('Error assigning surat:', error)
    alert('Gagal meneruskan surat. Silakan coba lagi.')
  }
}

const downloadFile = (surat) => {
  if (surat.file_surat) {
    window.open(getFileUrl(surat.file_surat), '_blank')
  } else {
    alert('Tidak ada file yang tersedia untuk surat ini.')
  }
}

const getFileUrl = (filePath) => {
  if (!filePath) return '#'
  // Sesuaikan dengan URL storage Anda
  return `/storage/${filePath}`
}

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'pending'
    case 'diteruskan': return 'forwarded'
    case 'diproses': return 'processing'
    case 'selesai': return 'completed'
    case 'belum dibaca': return 'unread'
    default: return ''
  }
}

const formatStatus = (status) => {
  switch (status) {
    case 'pending': return 'Menunggu Proses'
    case 'diteruskan': return 'Diteruskan'
    case 'diproses': return 'Diproses'
    case 'selesai': return 'Selesai'
    case 'belum dibaca': return 'Belum Dibaca'
    default: return status
  }
}

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

// --- LIFECYCLE ---
onMounted(() => {
  fetchSurat()
  fetchCoordinators()
})
</script>

<style scoped>
/* --- General Layout --- */
.kelola-surat-container {
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

/* --- Style untuk Kartu Ringkasan --- */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.summary-card {
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1.5rem;
  border-left: 5px solid #007bce;
}

.summary-card.pending {
  border-left-color: #ffc107;
}

.summary-card.processing {
  border-left-color: #17a2b8;
}

.summary-card.completed {
  border-left-color: #28a745;
}

.summary-card i {
  font-size: 2.5rem;
  color: #007bce;
}

.summary-card.pending i {
  color: #ffc107;
}

.summary-card.processing i {
  color: #17a2b8;
}

.summary-card.completed i {
  color: #28a745;
}

.summary-info h3 {
  margin: 0;
  color: #495057;
  font-size: 1rem;
  font-weight: 600;
}

.summary-info p {
  margin: 0;
  color: #2c3e50;
  font-size: 2rem;
  font-weight: bold;
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

/* --- Filter Bar --- */
.filter-bar {
  margin-bottom: 1.5rem;
}

.filter-bar select {
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  background-color: white;
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

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

/* --- Status Badge --- */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-block;
}

.status-badge.small {
  padding: 0.15rem 0.5rem;
  font-size: 0.7rem;
}

.status-badge.pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-badge.forwarded {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-badge.processing {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-badge.completed {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.unread {
  background-color: #f8d7da;
  color: #721c24;
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

.btn-primary {
  background-color: #007bce;
  color: white;
}

.btn-primary:hover {
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
  background-color: #007bce;
  color: white;
}

.btn-view:hover {
  background-color: #0056b3;
  transform: scale(1.1);
}

.btn-download {
  background-color: #28a745;
  color: white;
}

.btn-download:hover {
  background-color: #1e7e34;
  transform: scale(1.1);
}

.btn-forward {
  background-color: #FF9800;
  color: white;
}

.btn-forward:hover {
  background-color: #e68900;
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
  max-width: 700px;
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

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h4 {
  color: #2c3e50;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  margin-bottom: 0.75rem;
}

.detail-row .label {
  font-weight: 600;
  color: #495057;
  width: 150px;
  flex-shrink: 0;
}

.detail-row .value {
  color: #2c3e50;
  flex-grow: 1;
}

.file-link {
  color: #007bce;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.file-link:hover {
  color: #0056b3;
  text-decoration: underline;
}

.disposition-item {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.disposition-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.assigned-to {
  font-weight: 600;
  color: #2c3e50;
}

.disposition-note {
  color: #6c757d;
  margin-bottom: 0.5rem;
}

.disposition-date {
  font-size: 0.8rem;
  color: #6c757d;
  text-align: right;
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

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}

.loading-indicator,
.no-data {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}

.loading-indicator i,
.no-data i {
  font-size: 2rem;
  margin-bottom: 1rem;
}
</style>