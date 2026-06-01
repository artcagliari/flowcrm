import api from './axios';

export const listDocuments = (params) => api.get('/documents', { params }).then((r) => r.data.data);

export const uploadDocument = (payload) => {
  const formData = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') formData.append(key, value);
  });

  return api.post('/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }).then((r) => r.data.data);
};

export const deleteDocument = (id) => api.delete(`/documents/${id}`).then((r) => r.data.data);

export const documentDownloadUrl = (id) => `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'}/documents/${id}/download`;

export const downloadDocument = async (document) => {
  const response = await api.get(`/documents/${document.id}/download`, { responseType: 'blob' });
  const url = window.URL.createObjectURL(new Blob([response.data]));
  const link = window.document.createElement('a');
  link.href = url;
  link.download = document.name;
  window.document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
};
