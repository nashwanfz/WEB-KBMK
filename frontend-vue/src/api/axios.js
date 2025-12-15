// src/api/axios.js
import axios from 'axios';

// 1. Buat instance Axios dengan konfigurasi dasar
const apiClient = axios.create({
  baseURL: 'https://kbmk.unmul.ac.id/api/api', 
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// 2. HAPUS request interceptor yang lama
// Kita akan menggantinya dengan fungsi yang lebih eksplisit

/*
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});
*/

// 3. TAMBAHKAN FUNGSI BARU UNTUK MENGATUR TOKEN
// Fungsi ini akan langsung mengubah header default instance Axios
const setAuthToken = (token) => {
  if (token) {
    // Atur header Authorization untuk semua request selanjutnya
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    console.log('✅ Axios: Token berhasil diatur.');
  } else {
    // Hapus header Authorization jika tidak ada token
    delete apiClient.defaults.headers.common['Authorization'];
    console.log('✅ Axios: Token berhasil dihapus.');
  }
};

// 4. Saat file ini dimuat, cek apakah ada token yang tersimpan
// Jika ada, atur token tersebut secara otomatis
const initialToken = localStorage.getItem('auth_token');
if (initialToken) {
  setAuthToken(initialToken);
}

// 5. Export instance dan fungsi setAuthToken
export default apiClient;
export { setAuthToken };