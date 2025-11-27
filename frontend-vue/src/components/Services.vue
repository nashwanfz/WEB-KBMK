<template>
  <div class="links-container">
    
    <div>
      <h1>Layanan</h1>
      <p>Akses cepat ke berbagai layanan dan formulir KBMK.</p>

      <div class="links-grid">
        <div v-for="link in importantLinks" :key="link.id" class="link-card">
          <h3>{{ link.name }}</h3>
          
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
import { ref } from 'vue';
import FormSurat from './FormSurat.vue'; // Pastikan path relatif ini benar

const showFormSurat = ref(false); 

const importantLinks = ref([
  {
    id: 1,
    name: 'Pendataan Mahasiswa Baru Kristen',
    url: 'https://docs.google.com/forms/d/e/1FAIpQLSdF39-Rlr-Q1tZCsZuioQCSefljUL0x4aNo0lcLWUDwDxhX7g/viewform',
    isInternal: false 
  },
  {
    id: 2,
    name: 'Complain Center',
    url: 'https://docs.google.com/forms/d/e/1FAIpQLSfPf_N7s3kCExKRcRJt1_hphcBTewMO0ZM-hWWUL2TX3kCSng/viewform?usp=send_form',
    isInternal: false
  },
  {
    id: 3,
    name: 'Pendataan Wisuda Pengurus KBMK',
    url: 'https://docs.google.com/forms/d/e/1FAIpQLScc-f2uolrfm58ldqYeWNRA28xAdTCOxx_g52ShnhgP13UFbA/viewform',
    isInternal: false
  },
  {
    id: 4,
    name: 'Peminjaman Barang',
    url: 'https://docs.google.com/forms/d/1E0-PpGHbRsdyATfSVe6Au9CU1BdLtzUbBJI9MWb-PnQ/viewform?edit_requested=true',
    isInternal: false
  },
  {
    id: 5,
    name: 'Peminjaman Tempat',
    url: 'https://docs.google.com/forms/d/1ZgoT9mMRfVlbmWX7zD1BN7EVz7xVVv_6q6O3U0y8UDA/viewform?edit_requested=true',
    isInternal: false
  },
  {
    id: 6,
    name: 'Kotak Surat',
    url: '#', 
    isInternal: true 
  },
]);
</script>

<style scoped>
/* Pastikan styling ini digunakan untuk tampilan Services yang konsisten */
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