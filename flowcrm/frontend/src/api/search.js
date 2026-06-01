import api from './axios';

export const globalSearch = (query) => api.get('/search', { params: { query } }).then((r) => r.data.data);
