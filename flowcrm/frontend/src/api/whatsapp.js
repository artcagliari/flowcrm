import api from './axios';

export const listConversations = (params) => api.get('/whatsapp/conversations', { params }).then((r) => r.data.data);
export const listMessages = (id, params) => api.get(`/whatsapp/conversations/${id}/messages`, { params }).then((r) => r.data.data);
export const sendWhatsappMessage = (id, body) => api.post(`/whatsapp/conversations/${id}/messages`, { body }).then((r) => r.data.data);
export const startConversation = (payload) => api.post('/whatsapp/conversations/start', payload).then((r) => r.data.data);
export const markConversationRead = (id) => api.patch(`/whatsapp/conversations/${id}/read`).then((r) => r.data.data);
export const getWhatsappUnread = () => api.get('/whatsapp/unread-count').then((r) => r.data.data);
export const getWhatsappSettings = () => api.get('/whatsapp/settings').then((r) => r.data.data);
export const saveWhatsappSettings = (payload) => api.put('/whatsapp/settings', payload).then((r) => r.data.data);
export const testWhatsapp = (phone) => api.post('/whatsapp/test', { phone }).then((r) => r.data);
