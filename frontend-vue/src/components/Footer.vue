<template>
  <footer class="main-footer">
    <div class="container footer-content">
      
      <!-- Kolom Informasi -->
      <div class="footer-info">
        <h4>Lokasi Sekretariat</h4>
        <p>Studeny Center, Lantai 3<br>Jl. Barong Tongkok, Gn. Kelua, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75242</p>
      </div>

      <!-- Kolom Kontak -->
      <div class="footer-contact">
        <h4>Kontak Pengurus</h4>
        <p>Ketua: Yonathan (0813-5140-1742)</p>
        <p>Wakil Ketua: Putri Juwita (0858-2823-7355)</p>
      </div>

      <!-- Kolom Media Sosial -->
      <div class="footer-social">
        <h4>Ikuti Kami</h4>
        <div class="social-links">
          <a href="https://www.instagram.com/kbmk_unmul?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" rel="noopener noreferrer" aria-label="Instagram KBMK Unmul">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.058 1.644-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.281-.058-1.689-.072-4.948-.072zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.162 6.162 6.162 6.162-2.759 6.162-6.162-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4s1.791-4 4-4 4 1.79 4 4-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.441 1.441 1.441 1.441-.645 1.441-1.441-.645-1.44-1.441-1.44z"/></svg>
          </a>
          <a href="https://www.facebook.com/kbmk.universitasmulawarman" target="_blank" rel="noopener noreferrer" aria-label="Facebook KBMK Unmul">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v2.385z"/></svg>
          </a>
          <a href="http://www.youtube.com/@kbmkunmul7135" target="_blank" rel="noopener noreferrer" aria-label="YouTube KBMK Unmul">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
          </a>
        </div>
      </div>

    </div>
    
    <!-- Bagian Copyright di Bawah -->
    <div class="footer-bottom">
      <p>&copy; 2025 KBMK Univeritas Mulawarman. All Rights Reserved.</p>
    </div>
  </footer>
</template>

<style scoped>
.main-footer {
  background-color: #3fa8e5;
  color: white;
  padding: 2.5rem 1rem 1rem 1rem;
  margin-top: auto; 
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 2rem;
  padding: 0 1rem;
  margin-bottom: 2rem;
}

.footer-info, .footer-contact, .footer-social {
  flex: 1;
  min-width: 280px;
}

.footer-info h4, .footer-contact h4, .footer-social h4 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: bold;
  border-bottom: 2px solid rgba(255, 255, 255, 0.3);
  padding-bottom: 0.5rem;
}

.footer-info p, .footer-contact p {
  margin: 0.5rem 0;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.9);
}

.social-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.social-links a {
  color: white;
  text-decoration: none;
  transition: transform 0.3s ease;
}

.social-links a:hover {
  transform: scale(1.2);
}

.footer-bottom {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}
</style>

