<template>
  <div class="profil-container">
    <div class="page-header">
      <h1>Profil KBMK</h1>
      <button class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Profil
      </button>
    </div>

    <div class="profil-content">
      <!-- PERBAIKAN: Gunakan computed property yang sudah dibuat -->
      <h2>{{ profilWithParagraphs.namaOrganisasi }}</h2>
      <div class="profil-description">
        <p v-for="(paragraf, index) in profilWithParagraphs.deskripsi" :key="index">
          {{ paragraf }}
        </p>
      </div>
    </div>

    <!-- Modal untuk Lihat dan Edit Profil -->
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
              <label for="namaOrganisasi">Nama Organisasi</label>
              <input type="text" id="namaOrganisasi" v-model="formData.namaOrganisasi" :disabled="modalMode === 'view'" required />
            </div>
            <div class="form-group">
              <label for="deskripsi">Deskripsi Profil</label>
              <textarea id="deskripsi" v-model="formData.deskripsiText" :disabled="modalMode === 'view'" required rows="8"></textarea>
              <p class="photo-hint" v-if="modalMode !== 'view'">*Pisahkan setiap paragraf dengan satu baris kosong.</p>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button v-if="modalMode === 'view'" class="btn btn-secondary" @click="closeModal">Tutup</button>
          <template v-else>
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" @click="handleSave">Simpan Perubahan</button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// --- MOCK DATA ---
// Data profil disimpan sebagai objek tunggal
const profil = ref({
  namaOrganisasi: 'Keluarga Besar Mahasiswa Kristen (KBMK)',
  deskripsiText: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.`
})

// --- STATE ---
const showModal = ref(false)
const modalMode = ref('edit') // 'view' atau 'edit'
const formData = ref({
  namaOrganisasi: '',
  deskripsiText: ''
})

// --- COMPUTED ---
// Memecah teks deskripsi menjadi array paragraf untuk ditampilkan
const profilWithParagraphs = computed(() => {
  return {
    ...profil.value,
    deskripsi: profil.value.deskripsiText.split(/\n\s*\n/).filter(p => p.trim() !== '')
  }
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'view': return 'Detail Profil KBMK'
    case 'edit': return 'Edit Profil KBMK'
    default: return 'Profil KBMK'
  }
})

// --- METHODS ---
const openModal = (mode) => {
  modalMode.value = mode
  // Salin data ke form untuk diedit atau dilihat
  formData.value = {
    namaOrganisasi: profil.value.namaOrganisasi,
    deskripsiText: profil.value.deskripsiText
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = () => {
  // Simpan perubahan dari form ke data utama
  profil.value.namaOrganisasi = formData.value.namaOrganisasi
  profil.value.deskripsiText = formData.value.deskripsiText
  
  alert('Profil KBMK berhasil diperbarui!')
  closeModal()
}
</script>

<style scoped>
/* --- General Layout --- */
.profil-container {
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

/* --- Content Display --- */
.profil-content {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.profil-content h2 {
  color: #007bce;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.profil-description p {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
  text-align: justify;
  margin-bottom: 1rem;
}

.profil-description p:last-child {
  margin-bottom: 0;
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
  max-width: 600px; /* Lebih lebar untuk textarea */
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit; /* Gunakan font yang sama */
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}
</style>