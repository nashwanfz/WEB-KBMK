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

    <!-- Tampilkan indikator loading -->
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
            <th>Tanggal Dibuat/Diperbarui</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="anggota in filteredAnggota" :key="anggota.id">
            <td>
              <img :src="anggota.foto_url || getDefaultPhoto()" alt="Foto" class="kegiatan-photo" />
            </td>
            <td>{{ anggota.nama }}</td>
            <td>{{ anggota.jabatan }}</td>
            <td>{{ anggota.divisi }}</td>
            <td>{{ formatDate(anggota.created_at) }}</td>
            <td class="action-buttons">
              <button class="btn-icon btn-view" @click="openModal('view', anggota)" title="Lihat Detail">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn-icon btn-edit" @click="openModal('edit', anggota)" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
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
            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" v-model="formData.deskripsi" :disabled="modalMode === 'view'" required rows="3"></textarea>
            </div>
            <div class="form-group" v-if="modalMode !== 'view'">
              <label for="foto">Foto</label>
              <input type="file" id="foto" @change="handlePhotoUpload" accept="image/*" />
              <p class="photo-hint">*Pilih file gambar untuk foto anggota.</p>
            </div>
            <div class="form-group" v-if="modalMode === 'view' && formData.foto_url">
              <label>Foto Saat Ini</label>
              <img :src="formData.foto_url" alt="Foto Anggota" class="current-photo" />
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
import { ref, computed, onMounted } from 'vue'
import { pengurusService } from '../api/pengurusService';

// --- STATE ---
const pengurusList = ref([]);
const searchQuery = ref('');
const isLoading = ref(false);
const showModal = ref(false);
const modalMode = ref('create'); // 'create', 'view', 'edit'
const formData = ref({
  id: null,
  nama: '',
  jabatan: '',
  divisi: '',
  deskripsi: '',
  foto: null, // Simpan objek File, bukan string base64
  foto_url: null // Simpan URL foto dari backend
});

// --- COMPUTED ---
const filteredAnggota = computed(() => {
  if (!searchQuery.value) {
    return pengurusList.value;
  }
  const query = searchQuery.value.toLowerCase();
  return pengurusList.value.filter(anggota =>
    anggota.nama.toLowerCase().includes(query) ||
    anggota.jabatan.toLowerCase().includes(query)
  );
});

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'create': return 'Tambah Anggota Baru';
    case 'view': return 'Detail Anggota';
    case 'edit': return 'Edit Anggota';
    default: return 'Form Anggota';
  }
});

// --- METHODS ---
const getDefaultPhoto = () => {
  return 'https://i.pravatar.cc/150?d=mp';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const openModal = (mode, anggota = null) => {
  modalMode.value = mode;
  if (mode === 'create') {
    formData.value = { id: null, nama: '', jabatan: '', divisi: '', deskripsi: '', foto: null, foto_url: null };
  } else {
    // Salin data, termasuk URL foto
    formData.value = { ...anggota, foto: null }; // foto tidak perlu disalin
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const fetchPengurus = async () => {
  isLoading.value = true;
  try {
    const response = await pengurusService.getPengurus();
    pengurusList.value = response.data.data; // Asumsi struktur response dari API Anda
  } catch (error) {
    console.error('Gagal mengambil data pengurus:', error);
    alert('Gagal memuat data pengurus. Silakan coba lagi.');
  } finally {
    isLoading.value = false;
  }
};

const handleSave = async () => {
  try {
    let response;
    if (modalMode.value === 'create') {
      response = await pengurusService.createPengurus(formData.value);
      alert('Anggota berhasil ditambahkan!');
    } else if (modalMode.value === 'edit') {
      response = await pengurusService.updatePengurus(formData.value.id, formData.value);
      alert('Data anggota berhasil diperbarui!');
    }
    
    // Refresh data dari server setelah berhasil
    await fetchPengurus();
    closeModal();
  } catch (error) {
    console.error('Gagal menyimpan data:', error);
    alert('Gagal menyimpan data anggota. Silakan coba lagi.');
  }
};

const handleDelete = async (anggota) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${anggota.nama}"?`)) {
    try {
      await pengurusService.deletePengurus(anggota.id);
      alert('Anggota berhasil dihapus.');
      await fetchPengurus(); // Refresh data
    } catch (error) {
      console.error('Gagal menghapus data:', error);
      alert('Gagal menghapus data anggota. Silakan coba lagi.');
    }
  }
};

const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Simpan objek file ke formData.value untuk dikirim ke backend
    formData.value.foto = file;
    
    // Buat URL preview sementara (opsional, untuk ditampilkan di modal)
    const reader = new FileReader();
    reader.onload = (e) => {
      formData.value.foto_url = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Ambil data saat komponen pertama kali dimuat
onMounted(() => {
  fetchPengurus();
});
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
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
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