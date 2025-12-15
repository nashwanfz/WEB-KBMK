<template>
  <div class="form-container">
    <div class="form-content"> 
        <h2>Formulir Pengajuan Surat</h2>
        
        <form @submit.prevent="kirimSurat">
            <div class="form-group">
                <label for="namaPengirim">Nama Pengirim:</label>
                <input type="text" id="namaPengirim" v-model="formData.nama_pengirim" required />
            </div>

            <div class="form-group">
                <label for="emailPengirim">Email Pengirim:</label>
                <input type="email" id="emailPengirim" v-model="formData.email_pengirim" />
            </div>

            <div class="form-group">
                <label for="perihal">Perihal:</label>
                <input type="text" id="perihal" v-model="formData.perihal" required />
            </div>

            <div class="form-group">
                <label for="tujuan">Tujuan:</label>
                <textarea id="tujuan" v-model="formData.tujuan" required></textarea>
            </div>

            <div class="form-group">
                <label for="asalInstansi">Asal Instansi:</label>
                <input type="text" id="asalInstansi" v-model="formData.asal_instansi" />
            </div>

            <div class="form-group">
                <label for="jenisSurat">Jenis Surat:</label>
                <select id="jenisSurat" v-model="formData.jenis_surat" required>
                    <option value="" disabled>Pilih Jenis Surat</option>
                    <option v-for="jenis in jenisSuratOptions" :key="jenis" :value="jenis">
                        {{ jenis }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="fileSurat">File Surat (PDF/DOC/DOCX):</label>
                <input type="file" id="fileSurat" @change="handleFileUpload" accept=".pdf,.doc,.docx" required />
                <p v-if="formData.file_surat" class="file-status">File Terpilih: {{ formData.file_surat.name }}</p>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Mengirim...' : 'Kirim Surat' }}
                </button>
                <button type="button" @click="resetAndCloseForm" class="btn btn-secondary" :disabled="isSubmitting">
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
import axios from 'axios';

const emit = defineEmits(['batal']);

// Data form disesuaikan dengan field yang ada di backend
const formData = ref({
  nama_pengirim: '',
  email_pengirim: '',
  perihal: '',
  tujuan: '',
  asal_instansi: '',
  jenis_surat: '',
  file_surat: null,
});

const jenisSuratOptions = ref(['Undangan', 'Permintaan Kerja Sama', 'Peminjaman Barang', 'Peminjaman Tempat', 'Lainnya']);
const pesanSukses = ref('');
const pesanError = ref('');
const isSubmitting = ref(false);

const handleFileUpload = (event) => {
  formData.value.file_surat = event.target.files[0];
};

const kirimSurat = async () => {
  pesanSukses.value = '';
  pesanError.value = '';
  isSubmitting.value = true;
  
  try {
    const dataToSend = new FormData();
    
    // Tambahkan semua field ke FormData
    Object.keys(formData.value).forEach(key => {
      if (formData.value[key] !== null) { // Pastikan tidak mengirim null
        dataToSend.append(key, formData.value[key]);
      }
    });
    
    // Kirim ke API dengan URL lengkap
    const response = await axios.post('https://kbmk.unmul.ac.id/api/api/surat-requests', dataToSend, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    pesanSukses.value = response.data.message || 'Surat berhasil diajukan!';
    
    // Tutup modal setelah 2 detik
    setTimeout(() => {
      resetAndCloseForm(); 
    }, 2000);
    
  } catch (error) {
    console.error('Error submitting form:', error);
    
    if (error.response && error.response.data) {
        // Jika error validasi, tampilkan pesan pertama
        if (error.response.data.errors) {
            const firstError = Object.values(error.response.data.errors)[0];
            pesanError.value = Array.isArray(firstError) ? firstError[0] : firstError;
        } else if (error.response.data.message) {
            pesanError.value = error.response.data.message;
        }
    } else {
      pesanError.value = 'Terjadi kesalahan saat mengirim surat. Silakan coba lagi.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const resetForm = () => {
  formData.value = { 
    nama_pengirim: '', 
    email_pengirim: '', 
    perihal: '', 
    tujuan: '', 
    asal_instansi: '', 
    jenis_surat: '', 
    file_surat: null 
  };
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
/* Style tetap sama seperti sebelumnya */
.form-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; overflow-y: auto; }
.form-content { background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); max-width: 600px; width: 90%; margin: 40px auto; }
h2 { text-align: center; color: #333; margin-bottom: 25px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
.form-group { margin-bottom: 20px; }
label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
input[type="text"], input[type="email"], select, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; transition: border-color 0.3s; }
textarea { min-height: 100px; resize: vertical; }
input[type="text"]:focus, input[type="email"]:focus, select:focus, textarea:focus { border-color: #007bff; outline: none; }
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