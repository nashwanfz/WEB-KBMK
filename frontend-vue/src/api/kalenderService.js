// src/api/kalenderService.js
import apiClient from './axios';

export const kalenderService = {
  // Mengambil semua data kegiatan
  getKalender: () => {
    return apiClient.get('/schedules');
  },

  // Menambah kegiatan baru
  createKalender: (formData) => {
    // Backend Anda menggunakan 'multipart/form-data', jadi kita juga menggunakan
    const data = new FormData();
    data.append('nama', formData.namaKegiatan);
    data.append('tanggalMulai', formData.tanggalMulai);
    data.append('tanggalSelesai', formData.tanggalSelesai);
    data.append('lokasi', formData.lokasi);
    data.append('deskripsi', formData.deskripsi);

    // Jika ada foto, tambahkan ke FormData
    if (formData.foto) {
      data.append('foto', formData.foto);
    }

    return apiClient.post('/schedules', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  // Memperbarui kegiatan
  updateKalender: (id, formData) => {
    const data = new FormData();
    data.append('_method', 'PUT'); // Laravel Resource Controller butuh 'PUT' dengan _method di FormData
    data.append('nama', formData.namaKegiatan);
    data.append('tanggalMulai', formData.tanggalMulai);
    data.append('tanggalSelesai', formData.tanggalSelesai);
    data.append('lokasi', formData.lokasi);
    data.append('deskripsi', formData.deskripsi);

    // Jika ada foto, tambahkan ke FormData
    if (formData.foto) {
      data.append('foto', formData.foto);
    }

    return apiClient.post(`/schedules/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  // Menghapus kegiatan
  deleteKalender: (id) => {
    return apiClient.delete(`/schedules/${id}`);
  }
};