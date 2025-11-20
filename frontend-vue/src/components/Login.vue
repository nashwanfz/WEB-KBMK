<script setup>
import { ref, inject, onMounted } from 'vue';

// Inject fungsi dari App.vue
const loginSuccess = inject('login-success');

// Emit untuk kembali ke beranda
const emit = defineEmits(['showHome']);

// State untuk form
const username = ref('');
const password = ref('');
const rememberMe = ref(false);
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false);

// Saat komponen dimuat, cek apakah ada username yang tersimpan
onMounted(() => {
  const savedUsername = localStorage.getItem('rememberedUser');
  if (savedUsername) {
    username.value = savedUsername;
    rememberMe.value = true;
  }
});

const handleLogin = async () => {
  // Reset pesan error
  errorMessage.value = '';
  
  // Validasi sederhana
  if (!username.value || !password.value) {
    errorMessage.value = 'Username dan password harus diisi.';
    return;
  }

  isLoading.value = true;

  // Simulasi delay untuk proses login (agar terasa nyata)
  await new Promise(resolve => setTimeout(resolve, 1000));

  if (username.value === 'Admin' && password.value === '123') {
    // Jika "Remember Me" dicentang, simpan username
    if (rememberMe.value) {
      localStorage.setItem('rememberedUser', username.value);
    } else {
      localStorage.removeItem('rememberedUser');
    }

    // Panggil fungsi login sukses dari App.vue
    if (loginSuccess) {
      loginSuccess();
    }
  } else {
    errorMessage.value = 'Username atau password salah.';
  }

  isLoading.value = false;
};

// Fungsi untuk toggle visibility password
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <section class="login-section">
    <div class="login-card">
      <div class="login-header">
        <img src="@/assets/logo.png" alt="Logo KBMK" class="login-logo">
        <h2>Login Admin</h2>
        <p>Silakan masuk untuk melanjutkan</p>
      </div>
      
      <form class="login-form" @submit.prevent="handleLogin">
        <!-- Tampilkan pesan error jika ada -->
        <div v-if="errorMessage" class="error-message">
          <i class="fas fa-exclamation-circle"></i>
          {{ errorMessage }}
        </div>

        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input 
              type="text" 
              id="username" 
              v-model="username" 
              placeholder="Masukkan username"
              required
              @input="errorMessage = ''"
            />
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input 
              :type="showPassword ? 'text' : 'password'"
              id="password" 
              v-model="password" 
              placeholder="Masukkan password"
              required
              @input="errorMessage = ''"
            />
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
          <span v-else>
            <i class="fas fa-spinner fa-spin"></i> Memproses...
          </span>
        </button>
        
         <div class="back-link">
          <a href="#" @click.prevent="emit('showHome')">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
          </a>
        </div>
      </form>
    </div>
  </section>
</template>

<style scoped>
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