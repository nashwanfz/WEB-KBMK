<template>
  <div class="slider-container" 
       @touchstart="handleTouchStart" 
       @touchend="handleTouchEnd">
    
    <!-- Gambar yang sedang ditampilkan -->
    <img :src="images[currentIndex]" alt="Foto Kegiatan" class="slider-image">

    <!-- Tombol Navigasi (hanya muncul jika gambar lebih dari 1) -->
    <template v-if="images.length > 1">
      <button @click.stop="prevImage" class="slider-btn prev-btn">&lt;</button>
      <button @click.stop="nextImage" class="slider-btn next-btn">&gt;</button>
    </template>

  </div>
</template>

<script setup>
import { ref } from 'vue';

// Menerima 'props' (properti) berupa array gambar dari komponen induk
const props = defineProps({
  images: {
    type: Array,
    required: true,
    default: () => []
  }
});

const currentIndex = ref(0);
const touchStartX = ref(0);

// Fungsi untuk ke gambar sebelumnya
const prevImage = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
  } else {
    // Kembali ke gambar terakhir jika sedang di gambar pertama
    currentIndex.value = props.images.length - 1;
  }
};

// Fungsi untuk ke gambar berikutnya
const nextImage = () => {
  if (currentIndex.value < props.images.length - 1) {
    currentIndex.value++;
  } else {
    // Kembali ke gambar pertama jika sedang di gambar terakhir
    currentIndex.value = 0;
  }
};

// Fungsi untuk menangani swipe
const handleTouchStart = (event) => {
  touchStartX.value = event.touches[0].clientX;
};

const handleTouchEnd = (event) => {
  const touchEndX = event.changedTouches[0].clientX;
  // Geser ke kanan (gambar sebelumnya)
  if (touchEndX > touchStartX.value + 50) {
    prevImage();
  } 
  // Geser ke kiri (gambar berikutnya)
  else if (touchEndX < touchStartX.value - 50) {
    nextImage();
  }
};
</script>

<style scoped>
.slider-container {
  position: relative;
  width: 100%;
  height: 180px; /* Sesuaikan dengan tinggi di card */
  overflow: hidden;
}

.slider-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: opacity 0.3s ease;
}

.slider-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: rgba(0, 0, 0, 0.4);
  color: white;
  border: none;
  font-size: 1.5rem;
  font-weight: bold;
  cursor: pointer;
  padding: 0.5rem;
  height: 100%;
  opacity: 0;
  transition: opacity 0.3s;
}

.slider-container:hover .slider-btn {
  opacity: 1;
}

.prev-btn {
  left: 0;
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}

.next-btn {
  right: 0;
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
}
</style>
