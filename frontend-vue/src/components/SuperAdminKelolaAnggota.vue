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
              <!-- MODIFIKASI: Gunakan fungsi getPhotoUrl untuk membuat URL yang benar -->
              <img :src="getPhotoUrl(anggota.foto_url)" alt="Foto" class="kegiatan-photo" />
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
          <form>
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
            <!-- MODIFIKASI: Tampilkan foto saat ini di mode view/edit -->
            <div class="form-group" v-if="modalMode === 'view' && formData.foto_url">
              <label>Foto Saat Ini</label>
              <img :src="getPhotoUrl(formData.foto_url)" alt="Foto Anggota" class="current-photo" />
            </div>
             <!-- MODIFIKASI: Tampilkan preview foto baru di mode edit jika ada -->
            <div class="form-group" v-if="modalMode === 'edit' && formData.foto_url && formData.foto_url.startsWith('data:')">
              <label>Preview Foto Baru</label>
              <img :src="formData.foto_url" alt="Preview Foto Baru" class="current-photo" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="button" class="btn btn-primary" @click="handleSave">Simpan</button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
// MODIFIKASI: Impor axios langsung, bukan pengurusService
import axios from 'axios';

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
  foto: null, // Simpan objek File untuk upload baru
  foto_url: null // Simpan URL untuk preview (base64 atau URL lama)
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

// MODIFIKASI: Fungsi baru untuk membuat URL foto yang benar
const getPhotoUrl = (foto) => {
  if (!foto) {
    return getDefaultPhoto();
  }
  // Jika 'foto' sudah berupa URL lengkap, gunakan saja
  if (foto.startsWith('http')) {
    return foto;
  }
  // Jika hanya path (misal: /storage/pengurus/foto.jpg), gabungkan dengan base URL
  return `http://localhost:8000${foto}`;
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
  // Reset form terlebih dahulu
  formData.value = { id: null, nama: '', jabatan: '', divisi: '', deskripsi: '', foto: null, foto_url: null };

  if (mode !== 'create' && anggota) {
    // Salin data anggota ke formData
    formData.value = { ...anggota, foto: null };
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

// MODIFIKASI: Ganti seluruh logika pengambilan data
const fetchPengurus = async () => {
  isLoading.value = true;
  try {
    // 1. Ambil data langsung menggunakan axios
    const res = await axios.get("http://localhost:8000/api/pengurus");
    const data = res.data.data;

    // 2. (Opsional) Kelompokkan pengurus berdasarkan divisi jika diperlukan di tempat lain
    // Bagian ini diambil dari kode Anda, meskipun tidak digunakan langsung di tabel
    const grouped = {};
    data.forEach((item) => {
      if (!grouped[item.divisi]) grouped[item.divisi] = [];
      grouped[item.divisi].push(item);
    });

    const divisions = Object.entries(grouped).map(([divisi, members]) => {
      const koordinator = members.reduce((min, m) => (m.id < min.id ? m : min));
      const sortedMembers = [koordinator, ...members.filter((m) => m.id !== koordinator.id)];
      return { divisi, members: sortedMembers };
    });
    console.log('Data grouped by division:', divisions); // Untuk debugging, bisa dihapus

    // 3. Ratakan kembali data untuk digunakan di tabel
    // Ini penting agar template tabel yang lama tetap berfungsi
    pengurusList.value = data;

  } catch (err) {
    console.error('Gagal mengambil data pengurus:', err);
    alert('Gagal memuat data pengurus. Silakan coba lagi.');
  } finally {
    isLoading.value = false;
  }
};

// MODIFIKASI: Fungsi handleSave yang sekarang menggunakan axios langsung
const handleSave = async () => {
  try {
    let response;
    if (modalMode.value === 'create') {
      const dataToSend = new FormData();
      dataToSend.append('nama', formData.value.nama);
      dataToSend.append('jabatan', formData.value.jabatan);
      dataToSend.append('divisi', formData.value.divisi);
      dataToSend.append('deskripsi', formData.value.deskripsi);
      if (formData.value.foto) {
        dataToSend.append('foto', formData.value.foto);
      }
      
      response = await axios.post("http://localhost:8000/api/pengurus", dataToSend, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      alert('Anggota berhasil ditambahkan!');
    } else if (modalMode.value === 'edit') {
      const dataToUpdate = new FormData();
      dataToUpdate.append('_method', 'PUT'); // Spoofing method untuk Laravel
      dataToUpdate.append('nama', formData.value.nama);
      dataToUpdate.append('jabatan', formData.value.jabatan);
      dataToUpdate.append('divisi', formData.value.divisi);
      dataToUpdate.append('deskripsi', formData.value.deskripsi);
      if (formData.value.foto instanceof File) {
        dataToUpdate.append('foto', formData.value.foto);
      }

      response = await axios.post(`http://localhost:8000/api/pengurus/${formData.value.id}`, dataToUpdate, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      alert('Data anggota berhasil diperbarui!');
    }
    
    await fetchPengurus();
    closeModal();
  } catch (error) {
    console.error('Gagal menyimpan data:', error);
    alert('Gagal menyimpan data anggota. Silakan coba lagi.');
  }
};

// MODIFIKASI: Fungsi handleDelete menggunakan axios
const handleDelete = async (anggota) => {
  if (confirm(`Apakah Anda yakin ingin menghapus "${anggota.nama}"?`)) {
    try {
      await axios.delete(`http://localhost:8000/api/pengurus/${anggota.id}`);
      alert('Anggota berhasil dihapus.');
      await fetchPengurus();
    } catch (error) {
      console.error('Gagal menghapus data:', error);
      alert('Gagal menghapus data anggota. Silakan coba lagi.');
    }
  }
};

const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    formData.value.foto = file;
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