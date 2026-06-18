import api from './axios';

export const getMyDashboard = () => api.get('/my-dashboard').then((r) => r.data.data);
export const listSalesGoals = (params) => api.get('/sales-goals', { params }).then((r) => r.data.data);
export const saveSalesGoal = (payload) => api.post('/sales-goals', payload).then((r) => r.data.data);
export const removeSalesGoal = (id) => api.delete(`/sales-goals/${id}`).then((r) => r.data.data);
export const listAuditLogs = (params) => api.get('/audit-logs', { params }).then((r) => r.data.data);
export const listWebhooks = () => api.get('/webhooks').then((r) => r.data.data);
export const createWebhook = (payload) => api.post('/webhooks', payload).then((r) => r.data.data);
export const updateWebhook = (id, payload) => api.put(`/webhooks/${id}`, payload).then((r) => r.data.data);
export const removeWebhook = (id) => api.delete(`/webhooks/${id}`).then((r) => r.data.data);
export const listIntegrations = () => api.get('/integrations').then((r) => r.data.data);
export const saveIntegration = (payload) => api.post('/integrations', payload).then((r) => r.data.data);
export const connectGoogleCalendar = () => api.get('/integrations/google-calendar/connect').then((r) => r.data.data);
export const disconnectGoogleCalendar = () => api.delete('/integrations/google-calendar').then((r) => r.data.data);
export const updateGoogleCalendar = (payload) => api.patch('/integrations/google-calendar', payload).then((r) => r.data.data);
export const syncGoogleCalendar = () => api.post('/integrations/google-calendar/sync').then((r) => r.data.data);
export const getTimeline = (type, id) => api.get(`/${type}/${id}/timeline`).then((r) => r.data.data);
export const exportClients = () => api.get('/export/clients', { responseType: 'blob' });
export const exportLeads = () => api.get('/export/leads', { responseType: 'blob' });
export const importClients = (file) => {
  const form = new FormData();
  form.append('file', file);
  return api.post('/import/clients', form).then((r) => r.data.data);
};
export const importLeads = (file) => {
  const form = new FormData();
  form.append('file', file);
  return api.post('/import/leads', form).then((r) => r.data.data);
};
export const getOpenApiUrl = () => `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'}/openapi.json`;
