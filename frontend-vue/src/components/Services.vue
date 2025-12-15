<template>
  <!-- Bagian template tidak berubah -->
  <div class="links-container">
    <div v-if="isLoading" class="loading-placeholder">
      <p>Memuat data layanan...</p>
    </div>
    <div v-else-if="error" class="error-placeholder">
      <p>{{ error }}</p>
    </div>
    <div v-else>
      <h1>Layanan</h1>
      <p>Akses cepat ke berbagai layanan dan formulir KBMK.</p>
      <div class="links-grid">
        <div v-for="link in importantLinks" :key="link.id" class="link-card">
          <h3>{{ link.nama }}</h3>
          
          <button 
            v-if="link.isInternal" 
            @click="showFormSurat = true" 
            class="gform-link"
          >
            Klik di sini
          </button>
          
          <a 
            v-else 
            :href="link.url" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="gform-link"
          >
            Klik di sini
          </a>
        </div>
      </div>
    </div>
    
    <FormSurat 
      v-if="showFormSurat" 
      @batal="showFormSurat = false" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import FormSurat from './FormSurat.vue';
import axios from 'axios';

const showFormSurat = ref(false);
const importantLinks = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchLinks = async () => {
  try {
    const response = await axios.get('https://kbmk.unmul.ac.id/api/api/links');
    
    let fetchedLinks = [];
    if (response.data && response.data.data) {
      fetchedLinks = response.data.data.map(link => ({
        id: link.id,
        nama: link.nama,
        // 👇 PERBAIKAN: Ubah 'link.url' menjadi 'link.link' sesuai nama kolom di database
        url: link.link, 
        isInternal: false
      }));
    }

    // Tambahkan link "Kotak Surat" secara statis
    fetchedLinks.push({
      id: 'internal-surat-form',
      nama: 'Kotak Surat',
      url: '#',
      isInternal: true
    });

    importantLinks.value = fetchedLinks;

    // 👍 TAMBAHAN: Log untuk debugging, hapus jika sudah tidak diperlukan
    console.log('Data links yang dimuat:', importantLinks.value);

  } catch (err) {
    console.error('Error fetching links:', err);
    error.value = 'Gagal memuat data layanan. Silakan coba lagi nanti.';
    
    importantLinks.value = [{
      id: 'internal-surat-form',
      nama: 'Kotak Surat',
      url: '#',
      isInternal: true
    }];
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchLinks);
</script>

<style scoped>
/* Bagian style tidak berubah */
.loading-placeholder, .error-placeholder {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}
.error-placeholder {
  color: #dc3545;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 8px;
}
.links-container { padding: 2rem 1rem; position: relative; }
h1 { text-align: center; color: #2c3e50; margin-bottom: 0.5rem; }
.links-container > p { text-align: center; color: #555; margin-bottom: 2.5rem; }
.links-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.2rem; max-width: 1200px; margin: auto; }
.link-card { background-color: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; align-items: center; text-align: center; min-height: 100px; transition: transform 0.3s, box-shadow 0.3s; }
.link-card:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
.link-card h3 { margin: 0 0 1rem; color: #2c3e50; font-size: 1.2rem; }
.gform-link { background-color: #007bff; color: white; padding: 0.6rem 1.5rem; border-radius: 50px; text-decoration: none; display: inline-block; border: none; cursor: pointer; font-weight: bold; transition: background-color 0.3s; }
.gform-link:hover { background-color: #0056b3; }
</style>