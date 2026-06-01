import api from './axios';

export const getSettings = () => api.get('/settings').then((r) => r.data.data);
export const updateSettings = (settings) => api.put('/settings', { settings }).then((r) => r.data.data);
export const updateTheme = (primary_color) => api.patch('/settings/theme', { primary_color }).then((r) => r.data.data);
