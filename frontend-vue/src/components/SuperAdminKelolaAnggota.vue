<template>
  <div class="kelola-anggota-container">
    <div class="page-header">
      <h1>Kelola Anggota</h1>
      <button class="btn btn-primary" @click="openModal('create')">
        <i class="fas fa-plus"></i> Tambah Anggota Baru
      </button>
    </div>

    <div class="search-bar">
      <input type="text" placeholder="Cari berdasarkan nama atau jabatan..." v-model="searchQuery" />
      <i class="fas fa-search"></i>
    </div>

    <!-- KEMBALI MENGGUNAKAN STRUKTUR TABEL -->
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nama Anggota</th>
            <th>Jabatan</th>
            <th>Divisi</th>
            <th>Tanggal Dibuat/Diperbarui</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="anggota in filteredAnggota" :key="anggota.id">
            <td>
              <!-- PERUBAHAN: Ganti class="member-photo" menjadi class="kegiatan-photo" -->
              <img :src="anggota.foto || getDefaultPhoto()" alt="Foto" class="kegiatan-photo" />
            </td>
            <td>{{ anggota.nama }}</td>
            <td>{{ anggota.jabatan }}</td>
            <td>{{ anggota.divisi }}</td>
            <td>{{ formatDate(anggota.tanggalDibuat) }}</td>
            <td class="action-buttons">
              <!-- Tombol Lihat (Biru) -->
              <button class="btn-icon btn-view" @click="openModal('view', anggota)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <!-- Tombol Edit (Oranye) -->
              <button class="btn-icon btn-edit" @click="openModal('edit', anggota)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <!-- Tombol Hapus (Merah) -->
              <button class="btn-icon btn-delete" @click="handleDelete(anggota)" title="Hapus">
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
              <label for="nama">Nama Anggota</label>
              <input type="text" id="nama" v-model="formData.nama" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="jabatan">Jabatan</label>
              <input type="text" id="jabatan" v-model="formData.jabatan" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="divisi">Divisi</label>
              <input type="text" id="divisi" v-model="formData.divisi" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="foto">Foto</label>
              <input type="file" id="foto" @change="handlePhotoUpload" accept="image/*" />
              <p class="photo-hint">*Upload foto untuk mengganti. Kosongkan jika tidak ingin mengubah.</p>
            </div>
            <div class="form-group" v-if="modalMode === 'view' && formData.foto">
              <label>Foto Saat Ini</label>
              <img :src="formData.foto" alt="Foto Anggota" class="current-photo" />
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
const anggotaList = ref([
  {
    id: 1,
    nama: 'Ahmad Fadli',
    jabatan: 'Ketua Umum',
    divisi: 'Kepengurusan',
    foto: 'https://i.pravatar.cc/150?img=11',
    tanggalDibuat: new Date('2023-01-10')
  },
  {
    id: 2,
    nama: 'Siti Nurhaliza',
    jabatan: 'Sekretaris',
    divisi: 'Kepengurusan',
    foto: 'https://i.pravatar.cc/150?img=32',
    tanggalDibuat: new Date('2023-02-15')
  },
  {
    id: 3,
    nama: 'Budi Santoso',
    jabatan: 'Kepala Divisi Humas',
    divisi: 'Humas',
    foto: 'https://i.pravatar.cc/150?img=45',
    tanggalDibuat: new Date('2023-03-20')
  },
  {
    id: 4,
    nama: 'Dewi Lestari',
    jabatan: 'Staff Divisi Akademik',
    divisi: 'Akademik',
    foto: null,
    tanggalDibuat: new Date('2023-04-05')
  }
])

// --- STATE ---
const searchQuery = ref('')
const showModal = ref(false)
const modalMode = ref('create') // 'create', 'view', 'edit'
const formData = ref({
  id: null,
  nama: '',
  jabatan: '',
  divisi: '',
  foto: null
})

// --- COMPUTED ---
const filteredAnggota = computed(() => {
  if (!searchQuery.value) {
    return anggotaList.value
  }
  const query = searchQuery.value.toLowerCase()
  return anggotaList.value.filter(anggota =>
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
const getDefaultPhoto = () => {
  return 'https://i.pravatar.cc/150?d=mp'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const openModal = (mode, anggota = null) => {
  modalMode.value = mode
  if (mode === 'create') {
    formData.value = { id: null, nama: '', jabatan: '', divisi: '', foto: null }
  } else {
    formData.value = { ...anggota }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = () => {
  if (modalMode.value === 'create') {
    const newId = Math.max(...anggotaList.value.map(a => a.id)) + 1
    anggotaList.value.push({
      ...formData.value,
      id: newId,
      tanggalDibuat: new Date()
    })
    alert('Anggota berhasil ditambahkan!')
  } else if (modalMode.value === 'edit') {
    const index = anggotaList.value.findIndex(a => a.id === formData.value.id)
    if (index !== -1) {
      anggotaList.value[index] = {
        ...formData.value,
        tanggalDibuat: new Date()
      }
      alert('Data anggota berhasil diperbarui!')
    }
  }
  closeModal()
}

const handleDelete = (anggota) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${anggota.nama}"?`)) {
    const index = anggotaList.value.findIndex(a => a.id === anggota.id)
    if (index !== -1) {
      anggotaList.value.splice(index, 1)
      alert('Anggota berhasil dihapus.')
    }
  }
}

const handlePhotoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      formData.value.foto = e.target.result
    }
    reader.readAsDataURL(file)
  }
}
</script>

<style scoped>
/* --- General Layout --- */
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

/* --- PERUBAHAN: Ganti style foto lingkaran dengan foto kotak --- */
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
.form-group input[type="file"] {
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