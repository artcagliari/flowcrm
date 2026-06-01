import api from './axios';
import { resource } from './resource';
const leads = resource('/leads');
export const getLeadDetails = (id) => api.get(`/leads/${id}`).then((r) => r.data.data);
export const convertLead = (id) => api.post(`/leads/${id}/convert`).then((r) => r.data.data);
export const markLeadLost = (id, lost_reason) => api.patch(`/leads/${id}/lost`, { lost_reason }).then((r) => r.data.data);
export default leads;
