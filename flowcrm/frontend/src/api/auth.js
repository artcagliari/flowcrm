import api from './axios';

export const login = (payload) => api.post('/login', payload).then((r) => r.data.data);
export const register = (payload) => api.post('/register', payload).then((r) => r.data.data);
export const logout = () => api.post('/logout').then((r) => r.data);
export const me = () => api.get('/me').then((r) => r.data.data);
