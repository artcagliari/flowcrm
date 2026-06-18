import api from './axios';
export const getDashboard = () => api.get('/dashboard').then((r) => r.data.data);
export const getMyDashboard = () => api.get('/my-dashboard').then((r) => r.data.data);

