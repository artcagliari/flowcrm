import api from './axios';
import { resource } from './resource';
const leads = resource('/leads');
export const getLeadDetails = (id) => api.get(`/leads/${id}`).then((r) => r.data.data);
export const forwardContact = (id) => api.post(`/leads/${id}/convert`).then((r) => r.data.data);
export const convertLead = forwardContact;
export const discardContact = (id, payload) => api.patch(`/leads/${id}/lost`, payload).then((r) => r.data.data);
export const markLeadLost = discardContact;

export const createLeadTask = (id, payload) => api.post('/tasks', { ...payload, lead_id: id }).then((r) => r.data.data);
export const createLeadAppointment = (id, payload) => api.post('/appointments', { ...payload, lead_id: id }).then((r) => r.data.data);
export const createLeadNote = (id, payload) => api.post('/notes', { ...payload, lead_id: id }).then((r) => r.data.data);
export const uploadLeadDocument = (id, payload) => {
  const formData = new FormData();
  Object.entries({ ...payload, lead_id: id }).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') formData.append(key, value);
  });
  return api.post('/documents', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data.data);
};

export default leads;
