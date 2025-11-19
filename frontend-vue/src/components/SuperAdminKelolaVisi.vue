<template>
  <div class="visi-misi-container">
    <div class="page-header">
      <h1>Visi & Misi</h1>
      <button class="btn btn-primary" @click="openModal('edit')">
        <i class="fas fa-edit"></i> Edit Visi & Misi
      </button>
    </div>

    <div class="visi-misi-content">
      <div class="content-section">
        <h2>Visi</h2>
        <div class="section-text">
          <p v-for="(paragraf, index) in visiMisiWithParagraphs.visi" :key="`v-${index}`">
            {{ paragraf }}
          </p>
        </div>
      </div>

      <div class="content-section">
        <h2>Misi</h2>
        <div class="section-text">
          <p v-for="(paragraf, index) in visiMisiWithParagraphs.misi" :key="`m-${index}`">
            {{ paragraf }}
          </p>
        </div>
      </div>
    </div>

    <!-- Modal untuk Edit Visi & Misi -->
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
              <label for="visi">Visi</label>
              <textarea id="visi" v-model="formData.visiText" :disabled="modalMode === 'view'" required rows="5"></textarea>
              <p class="photo-hint" v-if="modalMode !== 'view'">*Tuliskan visi organisasi.</p>
            </div>
            <div class="form-group">
              <label for="misi">Misi</label>
              <textarea id="misi" v-model="formData.misiText" :disabled="modalMode === 'view'" required rows="8"></textarea>
              <p class="photo-hint" v-if="modalMode !== 'view'">*Tuliskan misi organisasi, pisah setiap poin dengan baris baru.</p>
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
// Data visi dan misi disimpan dalam satu objek
const visiMisi = ref({
  visiText: `Menjadi wadah utama bagi mahasiswa komputer untuk mengembangkan potensi diri, profesionalisme, dan kebersamaan dalam bidang teknologi informasi.`,
  misiText: `1. Menyelenggarakan kegiatan pelatihan dan workshop yang relevan dengan perkembangan teknologi.\n2. Membangun jaringan yang kuat antar mahasiswa, alumni, dan profesional di industri.\n3. Mendorong inovasi dan kreativitas mahasiswa melalui proyek dan kompetisi.\n4. Menjadi mitra strategis bagi fakultas dalam mendukung kegiatan akademik dan kemahasiswaan.`
})

// --- STATE ---
const showModal = ref(false)
const modalMode = ref('edit') // 'view' atau 'edit'
const formData = ref({
  visiText: '',
  misiText: ''
})

// --- COMPUTED ---
// Memecah teks visi dan misi menjadi array paragraf/poin
const visiMisiWithParagraphs = computed(() => {
  return {
    visi: visiMisi.value.visiText.split(/\n\s*\n/).filter(p => p.trim() !== ''),
    misi: visiMisi.value.misiText.split('\n').filter(p => p.trim() !== '') // Misi dipisah per baris
  }
})

const modalTitle = computed(() => {
  switch (modalMode.value) {
    case 'view': return 'Detail Visi & Misi'
    case 'edit': return 'Edit Visi & Misi'
    default: return 'Visi & Misi'
  }
})

// --- METHODS ---
const openModal = (mode) => {
  modalMode.value = mode
  // Salin data ke form untuk diedit atau dilihat
  formData.value = {
    visiText: visiMisi.value.visiText,
    misiText: visiMisi.value.misiText
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleSave = () => {
  // Simpan perubahan dari form ke data utama
  visiMisi.value.visiText = formData.value.visiText
  visiMisi.value.misiText = formData.value.misiText
  
  alert('Visi & Misi berhasil diperbarui!')
  closeModal()
}
</script>

<style scoped>
/* --- General Layout --- */
.visi-misi-container {
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
.visi-misi-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.content-section {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.content-section h2 {
  color: #007bce;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.section-text p {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
  text-align: justify;
  margin-bottom: 1rem;
}

.section-text p:last-child {
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
  max-width: 600px;
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
}
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

/* Responsive untuk layar lebih kecil */
@media (max-width: 768px) {
  .visi-misi-content {
    grid-template-columns: 1fr;
  }
}
</style>