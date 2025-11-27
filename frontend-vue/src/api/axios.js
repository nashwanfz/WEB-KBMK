// src/api/axios.js
import axios from 'axios';

// 1. Buat instance Axios dengan konfigurasi dasar
const apiClient = axios.create({
  // Ganti dengan Base URL dari api-docs.yaml Anda
  // Jika backend Anda ada di server yang sama (localhost:8000), gunakan ini.
  baseURL: 'http://localhost:8000/api', 
  
  // Atur header default untuk setiap request
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// 2. Request Interceptor: Menambahkan Token ke Setiap Request
// Ini akan berjalan SEBELUM setiap kali Anda mengirim request ke backend
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');

  // Jika token ada, tambahkan ke header Authorization
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
}, error => {
  // Jika ada error saat menyiapkan request (misal token tidak valid), hentikan proses
  return Promise.reject(error);
});

// 3. Response Interceptor: Menangani Error Global
// Ini akan berjalan SETELAH menerima response dari backend
apiClient.interceptors.response.use(
  response => response,
  error => {
    // Jika error adalah 401 (Unauthorized), artinya token tidak valid atau kadaluarsa
    if (error.response && error.response.status === 401) {
      // Hapus token yang tidak valid dari localStorage
      localStorage.removeItem('auth_token');
      
      // Alihkan user ke halaman login
      // Karena Anda tidak menggunakan Vue Router, kita bisa me-reload halaman
      window.location.href = '/login'; 
      
      // Atau, jika Anda menggunakan Vue Router, Anda bisa menggunakan router.push('/login')
      // import { useRouter } from 'vue';
      // const router = useRouter();
      // router.push('/login');
    }

    // Tolak promise agar error bisa ditangani di catch block
    return Promise.reject(error);
  }
);

// 4. Export instance yang sudah dikonfigurasi agar bisa digunakan di file lain
export default apiClient;