<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Konfigurasi API
const API_URL = 'http://localhost:8000/api'; // Sesuaikan dengan URL backend Anda

// Emit event ke App.vue
const emit = defineEmits(['login-success', 'show-home']);

// State untuk form
const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false);

// Fungsi untuk login
const handleLogin = async () => {
  errorMessage.value = '';
  
  if (!email.value || !password.value) {
    errorMessage.value = 'Email dan password harus diisi.';
    return;
  }

  isLoading.value = true;

  try {
    const api = axios.create({ baseURL: API_URL });
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    });

    const { token, user } = response.data.data;
    
    // Jika "Remember Me" dicentang, simpan email
    if (rememberMe.value) {
      localStorage.setItem('rememberedUser', email.value);
    } else {
      localStorage.removeItem('rememberedUser');
    }

    // Emit event login-success dengan data user dan token
    emit('login-success', { token, user });

  } catch (error) {
    if (error.response && error.response.status === 401) {
      errorMessage.value = 'Email atau password salah.';
    } else {
      errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.';
    }
  } finally {
    isLoading.value = false;
  }
};

// Fungsi untuk toggle visibility password
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<!-- Template dan Style tetap sama seperti yang Anda berikan -->
<template>
  <section class="login-section">
    <div class="login-card">
      <div class="login-header">
        <img src="@/assets/logo.png" alt="Logo KBMK" class="login-logo">
        <h2>Login Admin</h2>
        <p>Silakan masuk untuk melanjutkan</p>
      </div>
      
      <form class="login-form" @submit.prevent="handleLogin">
        <div v-if="errorMessage" class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          {{ errorMessage }}
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-with-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" v-model="email" placeholder="Masukkan email" required @input="errorMessage = ''" />
          </div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="password" placeholder="Masukkan password" required @input="errorMessage = ''" />
            <button type="button" class="password-toggle" @click="togglePasswordVisibility">
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>
        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" v-model="rememberMe">
            <span>Ingat saya</span>
          </label>
        </div>
        <button type="submit" class="login-btn" :disabled="isLoading">
          <span v-if="!isLoading">Masuk</span>
          <span v-else><i class="fas fa-spinner fa-spin"></i> Memproses...</span>
        </button>
        <div class="back-link">
          <a href="#" @click.prevent="emit('show-home')">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
          </a>
        </div>
      </form>
    </div>
  </section>
</template>

<style scoped>
/* ... Salin semua style CSS yang Anda berikan di sini ... */
.login-section {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  padding: 2rem;
  background-color: #f4f6f9;
}
.login-card {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 420px;
  border-top: 5px solid #007bce;
}
.login-header {
  text-align: center;
  margin-bottom: 30px;
}
.login-logo {
  width: 80px;
  height: 80px;
  margin-bottom: 1rem;
  border-radius: 50%;
  object-fit: cover;
}
.login-header h2 {
  color: #2c3e50;
  margin-bottom: 8px;
  font-size: 1.8rem;
}
.login-header p {
  color: #7f8c8d;
  font-size: 1rem;
}
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #495057;
}
.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}
.input-with-icon i {
  position: absolute;
  left: 15px;
  color: #aaa;
}
.input-with-icon input {
  width: 100%;
  padding: 12px 12px 12px 45px;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.input-with-icon input:focus {
  border-color: #007bce;
  box-shadow: 0 0 0 3px rgba(0, 123, 206, 0.1);
  outline: none;
}
.password-toggle {
  position: absolute;
  right: 15px;
  background: none;
  border: none;
  color: #aaa;
  cursor: pointer;
  padding: 5px;
}
.form-options {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin-bottom: 25px;
}
.remember-me {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
  color: #495057;
  cursor: pointer;
}
.remember-me input {
  margin-right: 8px;
  cursor: pointer;
}
.login-btn {
  width: 100%;
  background-color: #007bce;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.login-btn:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-2px);
}
.login-btn:disabled {
  background-color: #a0c8e0;
  cursor: not-allowed;
}
.error-message {
  color: #721c24;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 20px;
  text-align: center;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.back-link {
  text-align: center;
  margin-top: 20px;
  font-size: 0.9rem;
}
.back-link a {
  color: #007bce;
  text-decoration: none;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.3s;
}
.back-link a:hover {
  color: #0056b3;
  text-decoration: underline;
}
</style>