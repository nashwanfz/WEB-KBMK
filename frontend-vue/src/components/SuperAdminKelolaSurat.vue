<template>
  <div class="kelola-surat-container">
    <div class="page-header">
      <h1>Kelola Surat</h1>
      <button class="btn btn-primary" @click="openModal('create')">
        <i class="fas fa-plus"></i> Tambah Surat Baru
      </button>
    </div>

    <!-- Kartu Ringkasan Total Surat -->
    <div class="summary-card">
      <i class="fas fa-file-alt"></i>
      <div class="summary-info">
        <h3>Total Surat</h3>
        <p>{{ totalSurat }}</p>
      </div>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama, jenis, pengirim, atau instansi..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Surat</th>
            <th>Jenis Surat</th>
            <th>Tujuan</th>
            <!-- KOLOM BARU -->
            <th>Nama Pengirim</th>
            <th>Jenis Instansi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="surat in filteredSurat" :key="surat.id">
            <td>{{ surat.namaSurat }}</td>
            <td>{{ surat.jenisSurat }}</td>
            <td>{{ surat.tujuan }}</td>
            <!-- DATA BARU -->
            <td>{{ surat.namaPengirim }}</td>
            <td>{{ surat.jenisInstansi }}</td>
            <td class="action-buttons">
              <!-- Tombol Baca PDF (Biru) -->
              <button class="btn-icon btn-view" @click="handleRead(surat)" title="Baca Surat (PDF)">
                <i class="fas fa-file-pdf"></i>
              </button>
              <!-- Tombol Edit (Oranye) -->
              <button class="btn-icon btn-edit" @click="openModal('edit', surat)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <!-- Tombol Hapus (Merah) -->
              <button class="btn-icon btn-delete" @click="handleDelete(surat)" title="Hapus">
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
              <label for="namaSurat">Nama Surat</label>
              <input type="text" id="namaSurat" v-model="formData.namaSurat" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="jenisSurat">Jenis Surat</label>
              <input type="text" id="jenisSurat" v-model="formData.jenisSurat" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="tujuan">Tujuan</label>
              <input type="text" id="tujuan" v-model="formData.tujuan" :disabled="modalMode === 'view'" required />
            </div>
            <!-- FORM GROUP BARU -->
            <div class="form-group">
              <label for="namaPengirim">Nama Pengirim</label>
              <input type="text" id="namaPengirim" v-model="formData.namaPengirim" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="jenisInstansi">Jenis Instansi</label>
              <input type="text" id="jenisInstansi" v-model="formData.jenisInstansi" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="fileUrl">Link File PDF</label>
              <input type="url" id="fileUrl" v-model="formData.fileUrl" placeholder="https://example.com/file.pdf" />
              <p class="photo-hint">*Masukkan link langsung ke file PDF.</p>
            </div>
            <div class="form-group" v-if="modalMode === 'view' && formData.fileUrl">
              <label>Link File</label>
              <a :href="formData.fileUrl" target="_blank" class="gform-link">{{ formData.fileUrl }}</a>
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

// --- MOCK DATA (DIPERBARUI) ---
const suratList = ref([
  {
    id: 1,
    namaSurat: 'Surat Keterangan Aktif Organisasi',
    jenisSurat: 'Keterangan',
    tujuan: 'Pengajuan Beasiswa',
    // DATA BARU
    namaPengirim: 'Ketua Umum KBMK',
    jenisInstansi: 'Internal Universitas',
    fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
  },
  {
    id: 2,
    namaSurat: 'Undangan Rapat Koordinasi',
    jenisSurat: 'Undangan',
    tujuan: 'Pengurus Inti',
    // DATA BARU
    namaPengirim: 'Sekretaris KBMK',
    jenisInstansi: 'Internal Organisasi',
    fileUrl: 'https://www.adobe.com/support/products/enterprise/knowledgecenter/media/c4611_sample_explain.pdf'
  },
  {
    id: 3,
    namaSurat: 'Proposal Kegiatan Bakti Sosial',
    jenisSurat: 'Proposal',
    tujuan: 'Pencarian Dana Sponsorship',
    // DATA BARU
    namaPengirim: 'Kepala Divisi Humas',
    jenisInstansi: 'Eksternal (Perusahaan)',
    fileUrl: null
  }
])

// --- STATE (DIPERBARUI) ---
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create') // 'create', 'view', 'edit'
const formData = ref({
  id: null,
  namaSurat: '',
  jenisSurat: '',
  tujuan: '',
  // FIELD BARU
  namaPengirim: '',
  jenisInstansi: '',
  fileUrl: ''
})

// --- COMPUTED (DIPERBARUI) ---
const filteredSurat = computed(() => {
  if (!searchQuery.value) {
    return suratList.value
  }
  const query = searchQuery.value.toLowerCase()
  return suratList.value.filter(surat =>
    surat.namaSurat.toLowerCase().includes(query) ||
    surat.jenisSurat.toLowerCase().includes(query) ||
    // LOGIKA PENCARIAN BARU
    surat.namaPengirim.toLowerCase().includes(query) ||
    surat.jenisInstansi.toLowerCase().includes(query)
  )
})

const totalSurat = computed(() => {
  return suratList.value.length
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Surat Baru'
    case 'view': return 'Detail Surat'
    case 'edit': return 'Edit Surat'
    default: return 'Form Surat'
  }
})

// --- METHODS (DIPERBARUI) ---
const openModal = (mode, surat = null) => {
  modalMode.value = mode
  if (mode === 'create') {
    formData.value = { 
      id: null, 
      namaSurat: '', 
      jenisSurat: '', 
      tujuan: '', 
      // INISIALISASI BARU
      namaPengirim: '', 
      jenisInstansi: '', 
      fileUrl: '' 
    }
  } else {
    formData.value = { ...surat }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = () => {
  if (modalMode.value === 'create') {
    const newId = Math.max(...suratList.value.map(s => s.id)) + 1
    suratList.value.push({
      ...formData.value,
      id: newId
    })
    alert('Surat berhasil ditambahkan!')
  } else if (modalMode.value === 'edit') {
    const index = suratList.value.findIndex(s => s.id === formData.value.id)
    if (index !== -1) {
      suratList.value[index] = { ...formData.value }
      alert('Data surat berhasil diperbarui!')
    }
  }
  closeModal()
}

const handleDelete = (surat) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${surat.namaSurat}"?`)) {
    const index = suratList.value.findIndex(s => s.id === surat.id)
    if (index !== -1) {
      suratList.value.splice(index, 1)
      alert('Surat berhasil dihapus.')
    }
  }
}

// --- FUNGSI KHUSUS UNTUK MEMBUKA PDF ---
const handleRead = (surat) => {
  if (surat.fileUrl) {
    window.open(surat.fileUrl, '_blank')
  } else {
    alert('Tidak ada file PDF yang tersedia untuk surat ini.')
  }
}
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
.summary-card {
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  border-left: 5px solid #007bce;
}

.summary-card i {
  font-size: 2.5rem;
  color: #007bce;
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

/* --- Table --- */
.table-wrapper {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow-x: auto; /* Penting agar tabel tidak pecah di layar kecil */
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
  background-color: #d32f2f; 
  color: white; 
}
.btn-view:hover { 
  background-color: #b71c1c; 
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
  background-color: #607D8B; 
  color: white; 
}
.btn-delete:hover { 
  background-color: #455A64; 
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
.photo-hint {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.25rem;
  margin-bottom: 0;
}
.gform-link {
  color: #007bce;
  text-decoration: none;
  word-break: break-all;
}
.gform-link:hover {
  color: #0056b3;
  text-decoration: underline;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>