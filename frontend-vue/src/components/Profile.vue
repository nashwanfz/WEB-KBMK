<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import logoOrganisasi from "@/assets/logo.png";

const about = ref("");
const visi = ref("");
const misi = ref("");
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    const res = await axios.get("https://kbmk.unmul.ac.id/api/api/profile-descs");
    const data = res.data.data;

    // Ambil deskripsi sesuai jenis
    data.forEach((item) => {
      if (item.jenis === "about") {
        about.value = item.deskripsi;
      } else if (item.jenis === "visi") {
        visi.value = item.deskripsi;
      } else if (item.jenis === "misi") {
        misi.value = item.deskripsi;
      }
    });
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="profile-card">
    <div v-if="loading">Loading...</div>
    <div v-else-if="error">{{ error }}</div>
    <div v-else>
      <div class="profile-header">
        <img
          :src="logoOrganisasi"
          alt="Logo Organisasi"
          class="organization-logo"
        >
        <div class="organization-description">
          <h1>KBMK UNMUL</h1>
          <p>{{ about || "Deskripsi profil tidak tersedia" }}</p>
        </div>
      </div>

      <div class="visi-misi-section">
        <div class="visi">
          <h3>Visi</h3>
          <p>{{ visi || "Visi tidak tersedia" }}</p>
        </div>
        <div class="misi">
          <h3>Misi</h3>
          <p>{{ misi || "Misi tidak tersedia" }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-card {
  padding: 1rem;
  background-color: #fff;
  border-radius: 8px;
}

.profile-header {
  display: flex;
  align-items: flex-start;
  gap: 2rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 1.5rem;
  margin-bottom: 1.5rem;
}

.organization-logo {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 50%;
}

.organization-description h1 {
  margin-top: 0;
  margin-bottom: 0;
  color: #261465;
  font-size: 2.5rem;
}

.organization-description p {
  line-height: 1.6;
  color: #555;
}

.visi-misi-section h3 {
  color: #261465;
  border-left: 4px solid #261465;
  padding-left: 0.75rem;
  margin-bottom: 1rem;
}
</style>