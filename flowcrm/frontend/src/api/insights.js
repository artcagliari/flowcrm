import api from './axios';

export const getClientInsights = (id) => api.get(`/clients/${id}/insights`).then((r) => r.data.data);
export const getLeadInsights = (id) => api.get(`/leads/${id}/insights`).then((r) => r.data.data);
