import api from './axios';

export const listUsers = () => api.get('/users').then((response) => response.data.data);
export const createUser = (payload) => api.post('/users', payload).then((response) => response.data.data);
export const updateUser = (id, payload) => api.put(`/users/${id}`, payload).then((response) => response.data.data);
export const deleteUser = (id) => api.delete(`/users/${id}`).then((response) => response.data.data);
export const updateProfile = (payload) => api.put('/profile', payload).then((response) => response.data.data);
