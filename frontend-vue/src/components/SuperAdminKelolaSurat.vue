<template>
  <div class="kelola-surat-container">
    <div class="page-header">
      <h1>Kelola Surat Disposisi</h1>
    </div>

    <!-- Kartu Ringkasan Total Surat -->
    <div class="summary-cards">
      <div class="summary-card">
        <i class="fas fa-file-alt"></i>
        <div class="summary-info">
          <h3>Total Tugas</h3>
          <p>{{ totalSurat }}</p>
        </div>
      </div>
      <div class="summary-card pending">
        <i class="fas fa-clock"></i>
        <div class="summary-info">
          <h3>Menunggu Diproses</h3>
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
        <option value="pending">Menunggu Penugasan</option>
        <option value="belum dibaca">Menunggu Diproses (Lama)</option>
        <option value="diteruskan">Menunggu Diproses</option>
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
          <tr v-for="disposition in filteredSurat" :key="disposition.id">
            <!-- 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request' -->
            <td>{{ disposition.surat_request?.nomor_surat || '-' }}</td>
            <td>{{ disposition.surat_request?.nama_pengirim || '-' }}</td>
            <td>{{ disposition.surat_request?.perihal || '-' }}</td>
            <td>{{ disposition.surat_request?.tujuan || '-' }}</td>
            <td>{{ disposition.surat_request?.asal_instansi || '-' }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(disposition.status)]">
                {{ formatStatus(disposition.status) }}
              </span>
            </td>
            <td class="action-buttons">
              <button class="btn-icon btn-view" @click="openModal('view', disposition)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn-icon btn-download" @click="downloadFile(disposition)" title="Download File">
                <i class="fas fa-download"></i>
              </button>
              
              <button v-if="userRole === 'koor_divisi' && disposition.status !== 'selesai'" 
                      class="btn-icon btn-edit" 
                      @click="openModal('updateStatus', disposition)" 
                      title="Ubah Status">
                <i class="fas fa-edit"></i>
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

    <!-- Modal untuk Lihat Detail dan Update Status -->
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
              <!-- 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request' -->
              <div class="detail-row">
                <span class="label">Nomor Surat:</span>
                <span class="value">{{ formData.surat_request?.nomor_surat }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Nama Pengirim:</span>
                <span class="value">{{ formData.surat_request?.nama_pengirim }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Email Pengirim:</span>
                <span class="value">{{ formData.surat_request?.email_pengirim || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Perihal:</span>
                <span class="value">{{ formData.surat_request?.perihal }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Tujuan:</span>
                <span class="value">{{ formData.surat_request?.tujuan }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Asal Instansi:</span>
                <span class="value">{{ formData.surat_request?.asal_instansi || '-' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Jenis Surat:</span>
                <span class="value">{{ formData.surat_request?.jenis_surat }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Status Disposisi:</span>
                <span :class="['status-badge', getStatusClass(formData.status)]">
                  {{ formatStatus(formData.status) }}
                </span>
              </div>
              <div class="detail-row">
                <span class="label">File Surat:</span>
                <button @click="openPdfModal(formData.surat_request?.file_surat)" class="file-link-button">
                  <i class="fas fa-file-pdf"></i> Lihat File
                </button>
              </div>
            </div>
            <div v-if="formData.catatan" class="detail-section">
                <h4>Catatan Penugasan</h4>
                <p>{{ formData.catatan }}</p>
            </div>
          </div>
          
          <!-- Update Status Mode -->
          <div v-else-if="modalMode === 'updateStatus'">
            <form @submit.prevent="handleUpdateStatus">
              <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" v-model="updateStatusForm.status" required>
                  <option value="">-- Pilih Status --</option>
                  <option value="diproses">Diproses</option>
                  <option value="selesai">Selesai</option>
                </select>
              </div>
              <div class="form-group">
                <label for="catatan">Catatan:</label>
                <textarea id="catatan" v-model="updateStatusForm.catatan" rows="3"></textarea>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else-if="modalMode === 'updateStatus'">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleUpdateStatus">Simpan Status</button>
          </template>
        </div>
      </div>
    </div>

    <!-- Modal untuk Lihat PDF -->
    <div v-if="showPdfModal" class="pdf-modal-overlay" @click="closePdfModal">
      <div class="pdf-modal-content" @click.stop>
        <div class="pdf-modal-header">
          <h3>Pratinjau Surat</h3>
          <button class="close-btn" @click="closePdfModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="pdf-modal-body">
          <iframe v-if="pdfBlobUrl" :src="pdfBlobUrl" width="100%" height="100%" frameborder="0"></iframe>
          <div v-else class="pdf-error">
            <p>File tidak dapat dimuat.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/axios.js'

// --- STATE ---
const suratList = ref([])
const userRole = ref('')
const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const modalMode = ref('view')

// 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request'
const formData = ref({
  id: null,
  status: '',
  catatan: '',
  surat_request: {}
})

const updateStatusForm = ref({
  status: '',
  catatan: ''
})

const showPdfModal = ref(false)
const pdfBlobUrl = ref(null)

// --- METHODS ---
const fetchUserData = async () => {
  try {
    const response = await apiClient.get('/me');
    userRole.value = response.data.data.role; 
  } catch (error) {
    console.error('Gagal mengambil data user:', error);
  }
};

const fetchSurat = async () => {
  loading.value = true
  let endpoint = '';
  
  if (userRole.value === 'admin') {
    endpoint = '/surat-requests';
  } else if (userRole.value === 'koor_divisi') {
    endpoint = '/my-dispositions';
  } else {
    console.error('Role tidak dikenali:', userRole.value);
    loading.value = false;
    return;
  }

  try {
    const response = await apiClient.get(endpoint)
    suratList.value = response.data.data
  } catch (error) {
    console.error('Error fetching surat:', error)
    alert('Gagal memuat data surat. Silakan coba lagi.')
  } finally {
    loading.value = false
  }
}

// --- COMPUTED ---
const displayData = computed(() => {
  if (userRole.value === 'koor_divisi') {
    return suratList.value;
  }

  if (userRole.value === 'admin') {
    const flattenedDispositions = [];
    suratList.value.forEach(suratRequest => {
      if (suratRequest.dispositions && suratRequest.dispositions.length > 0) {
        suratRequest.dispositions.forEach(disposition => {
          // 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request'
          flattenedDispositions.push({
            ...disposition,
            surat_request: suratRequest
          });
        });
      } else {
        flattenedDispositions.push({
            id: `req-${suratRequest.id}`,
            status: suratRequest.status,
            catatan: null,
            assigned_to: null,
            // 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request'
            surat_request: suratRequest
        });
      }
    });
    return flattenedDispositions;
  }

  return [];
});

const filteredSurat = computed(() => {
  let result = displayData.value
  
  if (statusFilter.value) {
    result = result.filter(item => item.status === statusFilter.value)
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(item =>
      // 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request'
      item.surat_request?.nama_pengirim?.toLowerCase().includes(query) ||
      item.surat_request?.perihal?.toLowerCase().includes(query) ||
      item.surat_request?.tujuan?.toLowerCase().includes(query) ||
      (item.surat_request?.asal_instansi && item.surat_request.asal_instansi.toLowerCase().includes(query))
    )
  }
  
  return result
})

const totalSurat = computed(() => displayData.value.length)
const totalPending = computed(() => displayData.value.filter(d => d.status === 'diteruskan' || d.status === 'belum dibaca').length)
const totalProcessing = computed(() => displayData.value.filter(d => d.status === 'diproses').length)
const totalCompleted = computed(() => displayData.value.filter(d => d.status === 'selesai').length)

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'view': return 'Detail Surat'
    case 'updateStatus': return 'Ubah Status Surat'
    default: return 'Form Surat'
  }
})

const openModal = (mode, disposition = null) => {
  modalMode.value = mode
  if (mode === 'view' && disposition) {
    formData.value = { ...disposition }
  } else if (mode === 'updateStatus' && disposition) {
    formData.value = { ...disposition }
    updateStatusForm.value = { status: disposition.status, catatan: disposition.catatan || '' }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleUpdateStatus = async () => {
  try {
    if (typeof formData.value.id === 'string' && formData.value.id.startsWith('req-')) {
        alert('Tidak dapat mengubah status surat yang belum ditugaskan.');
        return;
    }

    await apiClient.patch(`/surat-dispositions/${formData.value.id}/status`, updateStatusForm.value)
    
    alert('Status berhasil diperbarui!')
    closeModal()
    fetchSurat();

  } catch (error) {
    console.error('Error updating status:', error)
    const message = error.response?.data?.message || 'Gagal memperbarui status. Silakan coba lagi.';
    alert(message)
  }
}

const downloadFile = (disposition) => {
  // 👇 PERBAIKAN: Ganti 'suratRequest' menjadi 'surat_request'
  if (disposition.surat_request?.file_surat) {
    window.open(`http://localhost:8000/storage/${disposition.surat_request.file_surat}`, '_blank')
  } else {
    alert('Tidak ada file yang tersedia untuk surat ini.')
  }
}

const getFileUrl = (filePath) => {
  if (!filePath) return '#'
  return `http://localhost:8000/files/${filePath}`
}

const openPdfModal = async (filePath) => {
  if (filePath) {
    const fullUrl = getFileUrl(filePath);
    try {
      const response = await fetch(fullUrl);
      if (!response.ok) throw new Error('Gagal mengambil file.');
      const blob = await response.blob();
      
      if (pdfBlobUrl.value) URL.revokeObjectURL(pdfBlobUrl.value);
      pdfBlobUrl.value = URL.createObjectURL(blob);
      showPdfModal.value = true;
    } catch (error) {
      console.error('Error fetching PDF:', error);
      alert('Gagal memuat file untuk ditampilkan.');
    }
  } else {
    alert('Tidak ada file yang tersedia untuk surat ini.');
  }
};

const closePdfModal = () => {
  showPdfModal.value = false;
  if (pdfBlobUrl.value) {
    URL.revokeObjectURL(pdfBlobUrl.value);
    pdfBlobUrl.value = null;
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'belum dibaca':
    case 'diteruskan': return 'forwarded'
    case 'diproses': return 'processing'
    case 'selesai': return 'completed'
    default: return ''
  }
}

const formatStatus = (status) => {
  switch (status) {
    case 'belum dibaca': 
    case 'diteruskan': return 'Diteruskan'
    case 'pending': return 'Menunggu Penugasan'
    case 'diproses': return 'Diproses'
    case 'selesai': return 'Selesai'
    default: return status
  }
}

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

// --- LIFECYCLE ---
onMounted(async () => {
  await fetchUserData();
  fetchSurat();
})
</script>

<style scoped>
/* ... (semua style tetap sama) ... */
.kelola-surat-container { padding: 1.5rem; background-color: #f8f9fa; min-height: 100vh; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-header h1 { color: #343a40; margin: 0; }
.summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.summary-card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; border-left: 4px solid #007bff; }
.summary-card.pending { border-left-color: #ffc107; }
.summary-card.processing { border-left-color: #17a2b8; }
.summary-card.completed { border-left-color: #28a745; }
.summary-card i { font-size: 2rem; color: #007bff; }
.summary-card.pending i { color: #ffc107; }
.summary-card.processing i { color: #17a2b8; }
.summary-card.completed i { color: #28a745; }
.summary-info h3 { margin: 0; font-size: 0.9rem; color: #6c757d; text-transform: uppercase; }
.summary-info p { margin: 0; font-size: 1.5rem; font-weight: bold; color: #343a40; }
.search-bar, .filter-bar { position: relative; margin-bottom: 1rem; }
.search-bar input { width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid #ced4da; border-radius: 4px; }
.search-bar i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
.filter-bar select { padding: 0.75rem 1rem; border: 1px solid #ced4da; border-radius: 4px; width: 200px; }
.table-wrapper { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; }
.data-table th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
.action-buttons { display: flex; gap: 0.5rem; }
.btn-icon { background: none; border: 1px solid #dee2e6; padding: 0.5rem; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.btn-icon:hover { background-color: #e9ecef; }
.btn-view { color: #007bff; }
.btn-download { color: #28a745; }
.btn-edit { color: #ffc107; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize; }
.status-badge.forwarded { background-color: #d1ecf1; color: #0c5460; }
.status-badge.processing { background-color: #d4edda; color: #155724; }
.status-badge.completed { background-color: #d4edda; color: #155724; }
.loading-indicator, .no-data { text-align: center; padding: 2rem; color: #6c757d; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; padding: 0; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
.modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; }
.close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem; }
.detail-section { margin-bottom: 1.5rem; }
.detail-section h4 { border-bottom: 2px solid #007bff; padding-bottom: 0.5rem; margin-bottom: 1rem; color: #343a40; }
.detail-row { display: flex; margin-bottom: 0.75rem; }
.detail-row .label { font-weight: 600; width: 150px; flex-shrink: 0; color: #495057; }
.detail-row .value { color: #212529; }
.file-link-button { background: none; border: none; color: #007bff; cursor: pointer; padding: 0; font-size: inherit; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.5rem; }
.file-link-button:hover { color: #0056b3; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 4px; }
.btn { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background-color 0.2s; }
.btn-primary { background-color: #007bff; color: white; }
.btn-primary:hover { background-color: #0056b3; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover { background-color: #545b62; }

/* Style untuk Modal PDF */
.pdf-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); display: flex; justify-content: center; align-items: center; z-index: 1050; }
.pdf-modal-content { width: 95%; height: 90%; background-color: #fff; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); display: flex; flex-direction: column; }
.pdf-modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
.pdf-modal-body { flex-grow: 1; padding: 0; position: relative; }
.pdf-modal-body iframe { border: none; border-radius: 0 0 8px 8px; }
.pdf-error { display: flex; justify-content: center; align-items: center; height: 100%; color: #6c757d; font-size: 1.2rem; }
</style>