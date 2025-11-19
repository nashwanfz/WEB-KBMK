<script setup>
import { ref } from 'vue';

const emit = defineEmits(['showHome']);

const username = ref('');
const password = ref('');
const rememberMe = ref(false);

const handleLogin = () => {
  console.log('Login attempt:', {
    username: username.value,
    password: password.value,
  });
};
</script>

<template>
  <section class="login-section">
    <div class="login-card">
      <div class="login-header">
        <h2>Login KBMK UNMUL</h2>
        <p>Silakan masuk untuk melanjutkan</p>
      </div>
      
      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="username">Username atau Email</label>
          <input 
            type="text" 
            id="username" 
            v-model="username" 
            placeholder="Masukkan username atau email"
            required
          />
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="Masukkan password"
            required
          />
        </div>
                
        <button type="submit" class="login-btn">Masuk</button>
        
         <div class="back-link">
          <a href="#" @click.prevent="emit('showHome')">Kembali ke Beranda</a>
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
}
.login-card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 30px;
  width: 100%;
  max-width: 400px;
}
.login-header {
  text-align: center;
  margin-bottom: 25px;
}
.login-header h2 {
  color: #0056b3;
  margin-bottom: 10px;
}
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}
.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  font-size: 0.9rem;
}
.remember-me {
  display: flex;
  align-items: center;
}
.remember-me input {
  margin-right: 8px;
}
.forgot-password, .register-link a {
  color: #0056b3;
  text-decoration: none;
}
.login-btn {
  width: 100%;
  background-color: #0056b3;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 12px;
  font-size: 1rem;
  cursor: pointer;
}
.register-link, .back-link {
  text-align: center;
  margin-top: 20px;
  font-size: 0.9rem;
}
.back-link a {
  color: #555;
  text-decoration: underline;
}
</style>
