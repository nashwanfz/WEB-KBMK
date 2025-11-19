<template>
  <div class="kelola-gform-container">
    <div class="page-header">
      <h1>Kelola Link GForm</h1>
      <button class="btn btn-primary" @click="openModal('create')">
        <i class="fas fa-plus"></i> Tambah Link Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama form..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div class="table-wrapper">
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
            <td>{{ gform.namaForm }}</td>
            <td>
              <a :href="gform.linkGform" target="_blank" class="gform-link">
                {{ gform.linkGform }}
              </a>
            </td>
            <td class="action-buttons">
              <!-- Tombol Lihat (Biru) -->
              <button class="btn-icon btn-view" @click="openModal('view', gform)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <!-- Tombol Edit (Oranye) -->
              <button class="btn-icon btn-edit" @click="openModal('edit', gform)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <!-- Tombol Hapus (Merah) -->
              <button class="btn-icon btn-delete" @click="handleDelete(gform)" title="Hapus">
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
              <label for="namaForm">Nama Form</label>
              <input type="text" id="namaForm" v-model="formData.namaForm" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="linkGform">Link GForm</label>
              <input type="url" id="linkGform" v-model="formData.linkGform" placeholder="https://forms.gle/..." :disabled="modalMode === 'view'" required />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleSave">Simpan</button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// --- MOCK DATA ---
const gformList = ref([
  {
    id: 1,
    namaForm: 'Pendaftaran Anggota Baru',
    linkGform: 'https://forms.gle/example1'
  },
  {
    id: 2,
    namaForm: 'Pengajuan Kegiatan',
    linkGform: 'https://forms.gle/example2'
  },
  {
    id: 3,
    namaForm: 'Surat Keterangan Aktif',
    linkGform: 'https://forms.gle/example3'
  },
  {
    id: 4,
    namaForm: 'Kuisioner Kepuasan',
    linkGform: 'https://forms.gle/example4'
  }
])

// --- STATE ---
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create') // 'create', 'view', 'edit'
const formData = ref({
  id: null,
  namaForm: '',
  linkGform: ''
})

// --- COMPUTED ---
const filteredGform = computed(() => {
  if (!searchQuery.value) {
    return gformList.value
  }
  const query = searchQuery.value.toLowerCase()
  return gformList.value.filter(gform =>
    gform.namaForm.toLowerCase().includes(query)
  )
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
const openModal = (mode, gform = null) => {
  modalMode.value = mode
  if (mode === 'create') {
    formData.value = { id: null, namaForm: '', linkGform: '' }
  } else {
    formData.value = { ...gform } // Salin data untuk diedit atau dilihat
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = () => {
  if (modalMode.value === 'create') {
    const newId = Math.max(...gformList.value.map(g => g.id)) + 1
    gformList.value.push({
      ...formData.value,
      id: newId
    })
    alert('Link GForm berhasil ditambahkan!')
  } else if (modalMode.value === 'edit') {
    const index = gformList.value.findIndex(g => g.id === formData.value.id)
    if (index !== -1) {
      gformList.value[index] = { ...formData.value }
      alert('Link GForm berhasil diperbarui!')
    }
  }
  closeModal()
}

const handleDelete = (gform) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${gform.namaForm}"?`)) {
    const index = gformList.value.findIndex(g => g.id === gform.id)
    if (index !== -1) {
      gformList.value.splice(index, 1)
      alert('Link GForm berhasil dihapus.')
    }
  }
}
</script>

<style scoped>
/* --- General Layout --- */
.kelola-gform-container {
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

.gform-link {
  color: #007bce;
  text-decoration: none;
  word-break: break-all; /* Memecah link panjang agar tidak merusak layout */
  transition: color 0.2s;
}

.gform-link:hover {
  color: #0056b3;
  text-decoration: underline;
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
.form-group input[type="url"] {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
}
.form-group input:disabled {
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