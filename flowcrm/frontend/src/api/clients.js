import api from './axios';
import { resource } from './resource';
export const getClientDetails = (id) => api.get(`/clients/${id}`).then((r) => r.data.data);
export const createClientTask = (id, payload) => api.post(`/clients/${id}/tasks`, payload).then((r) => r.data.data);
export const createClientAppointment = (id, payload) => api.post(`/clients/${id}/appointments`, payload).then((r) => r.data.data);
export const createClientPayment = (id, payload) => api.post(`/clients/${id}/payments`, payload).then((r) => r.data.data);
export const createClientNote = (id, payload) => api.post(`/clients/${id}/notes`, payload).then((r) => r.data.data);
export const uploadClientDocument = (id, payload) => {
  const formData = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') formData.append(key, value);
  });
  return api.post(`/clients/${id}/documents`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data.data);
};
export const exportClientData = (id) => api.get(`/clients/${id}/export-data`).then((r) => r.data);
export const anonymizeClient = (id) => api.post(`/clients/${id}/anonymize`).then((r) => r.data);
export default resource('/clients');
