<template>
  <div class="form-container">
    
    <div class="form-content"> 
        <h2>Formulir Pengajuan Surat</h2>
        
        <form @submit.prevent="kirimSurat">
            
            <div class="form-group">
                <label for="namaPengirim">Nama Pengirim:</label>
                <input type="text" id="namaPengirim" v-model="formData.namaPengirim" required />
            </div>

            <div class="form-group">
                <label for="asalInstansi">Asal Instansi:</label>
                <input type="text" id="asalInstansi" v-model="formData.asalInstansi" required />
            </div>

            <div class="form-group">
                <label for="jenisSurat">Jenis Surat:</label>
                <select id="jenisSurat" v-model="formData.jenisSurat" required>
                    <option value="" disabled>Pilih Jenis Surat</option>
                    <option v-for="jenis in jenisSuratOptions" :key="jenis" :value="jenis">
                        {{ jenis }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="fileSurat">File Surat (PDF/DOCX):</label>
                <input type="file" id="fileSurat" @change="handleFileUpload" required />
                <p v-if="formData.fileSurat" class="file-status">File Terpilih: **{{ formData.fileSurat.name }}**</p>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Mengirim...' : 'Kirim Surat' }}
                </button>

                <button 
                  type="button" 
                  @click="resetAndCloseForm" 
                  class="btn btn-secondary"
                  :disabled="isSubmitting"
                >
                  Batal
                </button>
            </div>

            <div v-if="pesanSukses" class="message success-message">✅ {{ pesanSukses }}</div>
            <div v-if="pesanError" class="message error-message">❌ {{ pesanError }}</div>
            
        </form>
    </div>
    
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['batal']);

const formData = ref({
  namaPengirim: '',
  asalInstansi: '',
  jenisSurat: '',
  fileSurat: null,
});

const jenisSuratOptions = ref(['Undangan', 'Permintaan Kerja Sama', 'Peminjaman Barang', 'Peminjaman Tempat','Lainnya']);
const pesanSukses = ref('');
const pesanError = ref('');
const isSubmitting = ref(false);

const handleFileUpload = (event) => {
  formData.value.fileSurat = event.target.files[0];
};

const kirimSurat = () => {
  pesanSukses.value = '';
  pesanError.value = '';
  isSubmitting.value = true;
  
  // Simulasi pengiriman API
  setTimeout(() => {
    isSubmitting.value = false;
    pesanSukses.value = `Surat berhasil dikirim!`;
    
    // Setelah sukses, bersihkan dan tutup modal setelah 2 detik
    setTimeout(() => {
        resetAndCloseForm(); 
    }, 2000);
    
  }, 2000);
};

const resetForm = () => {
  formData.value = { namaPengirim: '', asalInstansi: '', jenisSurat: '', fileSurat: null };
  pesanSukses.value = '';
  pesanError.value = '';
  
  const fileInput = document.getElementById('fileSurat');
  if (fileInput) {
    fileInput.value = '';
  }
};

const resetAndCloseForm = () => {
    resetForm();
    emit('batal'); 
}
</script>

<style scoped>
/* ==================================== */
/* STYLING MODAL POP-UP (OVERLAY) */
/* ==================================== */
.form-container { 
  position: fixed; /* Menempel di viewport */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6); /* Latar belakang gelap transparan */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000; /* Pastikan di atas semua elemen */
  overflow-y: auto; 
}

/* STYLING KONTEN FORMULIR */
.form-content {
    background-color: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    width: 90%; 
    margin: 40px auto; 
    /* Agar modal berada di tengah jika viewport lebih kecil */
}

/* ==================================== */
/* STYLING INPUTS (Diambil dari jawaban sebelumnya) */
/* ==================================== */
h2 { text-align: center; color: #333; margin-bottom: 25px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
.form-group { margin-bottom: 20px; }
label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; transition: border-color 0.3s; }
input[type="text"]:focus, select:focus { border-color: #007bff; outline: none; }
input[type="file"] { width: 100%; padding: 10px 0; }
.file-status { margin-top: 5px; font-size: 0.9rem; color: #007bff; }
.form-actions { margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end; }
.btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { background-color: #007bff; color: white; }
.btn-primary:hover:not(:disabled) { background-color: #0056b3; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover:not(:disabled) { background-color: #5a6268; }
.message { margin-top: 20px; padding: 15px; border-radius: 6px; font-weight: bold; }
.success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>