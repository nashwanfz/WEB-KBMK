// src/utils/api.js
import axios from 'axios';

// Buat instance axios dengan konfigurasi dasar
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'https://kbmk.unmul.ac.id/api', // Sesuaikan dengan URL API Laravel Anda
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor untuk menangani respons error
apiClient.interceptors.response.use(
  (response) => response, // Lewati jika sukses
  (error) => {
    // Jika ada respons error dari server
    if (error.response) {
      // Laravel mengirim error validasi 422 dengan struktur spesifik
      // Kita biarkan error ini dilempar agar bisa ditangani di komponen
      return Promise.reject(error.response);
    } else if (error.request) {
      // Request dibuat tapi tidak ada respons (masalah jaringan)
      console.error('Network Error:', error.request);
      return Promise.reject({ message: 'Tidak dapat terhubung ke server. Periksa koneksi Anda.' });
    } else {
      // Error lainnya
      console.error('Request Error:', error.message);
      return Promise.reject({ message: error.message });
    }
  }
);

export default apiClient;