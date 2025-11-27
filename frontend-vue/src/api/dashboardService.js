// src/api/dashboardService.js
import apiClient from './axios';

export const dashboardService = {
  /**
   * Mengambil semua data statistik yang dibutuhkan untuk dashboard.
   * Menggunakan Promise.all untuk mengambil data secara paralel agar lebih efisien.
   */
  getStats: async () => {
    try {
      // Mengambil data dari 3 endpoint secara bersamaan
      const [pengurusResponse, schedulesResponse, documentsResponse] = await Promise.all([
        apiClient.get('/pengurus'),
        apiClient.get('/schedules'),
        apiClient.get('/documentations')
      ]);

      // Menghitung total data dari response
      // Backend Laravel dengan API Resource biasanya menyediakan 'meta' dengan informasi paginasi
      const totalPengurus = pengurusResponse.data.meta?.total || pengurusResponse.data.data.length;
      const kegiatanAktif = schedulesResponse.data.data.length; // Diasumsikan semua kegiatan adalah 'aktif'
      const totalSurat = documentsResponse.data.meta?.total || documentsResponse.data.data.length;
      
      // Asumsi: Link GForm adalah bagian dari dokumen.
      // Jika backend memiliki cara memfilternya, Anda bisa menambahkan parameter, contoh:
      // const gformResponse = await apiClient.get('/documentations?category=gform');
      // const totalGForm = gformResponse.data.meta?.total || gformResponse.data.data.length;
      // Untuk saat ini, kita asumsikan semua dokumen adalah GForm.
      const totalGForm = documentsResponse.data.data.length;

      return {
        totalPengurus,
        kegiatanAktif,
        totalSurat,
        totalGForm
      };
    } catch (error) {
      console.error('Gagal mengambil data dashboard:', error);
      // Melempar error kembali agar bisa ditangani di komponen
      throw error;
    }
  }
};