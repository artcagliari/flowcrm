import api from './axios';

export const getAdminDashboard = () => api.get('/admin/dashboard').then((r) => r.data.data);
export const listAdminCompanies = (params) => api.get('/admin/companies', { params }).then((r) => r.data.data);
export const createAdminCompany = (payload) => api.post('/admin/companies', payload).then((r) => r.data);
export const getAdminCompany = (id) => api.get(`/admin/companies/${id}`).then((r) => r.data.data);
export const updateAdminCompany = (id, payload) => api.put(`/admin/companies/${id}`, payload).then((r) => r.data.data);
export const activateAdminCompany = (id) => api.patch(`/admin/companies/${id}/activate`).then((r) => r.data.data);
export const suspendAdminCompany = (id) => api.patch(`/admin/companies/${id}/suspend`).then((r) => r.data.data);
export const deactivateAdminCompany = (id) => api.patch(`/admin/companies/${id}/deactivate`).then((r) => r.data.data);
export const resetAdminCompanyPassword = (id, payload) => api.post(`/admin/companies/${id}/reset-password`, payload).then((r) => r.data);
export const listAdminPlans = () => api.get('/admin/plans').then((r) => r.data.data);
