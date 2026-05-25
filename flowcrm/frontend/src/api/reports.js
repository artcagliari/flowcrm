import api from './axios';

export const getReports = (params) => api.get('/reports', { params }).then((response) => response.data.data);
