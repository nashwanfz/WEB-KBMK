// src/api/pengurusService.js
import apiClient from './axios';

export const pengurusService = {
  getPengurus: () => {
    return apiClient.get('/pengurus');
  },
  getPengurusById: (id) => {
    return apiClient.get(`/pengurus/${id}`);
  },
  createPengurus: (formData) => {
    return apiClient.post('/pengurus', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },
  updatePengurus: (id, formData) => {
    return apiClient.post(`/pengurus/${id}`, formData, { // Gunakan POST dengan _method untuk spoofing PUT
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },
  deletePengurus: (id) => {
    return apiClient.delete(`/pengurus/${id}`);
  }
};