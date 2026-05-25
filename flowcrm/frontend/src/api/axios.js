import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('flowcrm_token');
  const companyId = localStorage.getItem('flowcrm_company_id');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  if (companyId) config.headers['X-Company-ID'] = companyId;
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('flowcrm_token');
      localStorage.removeItem('flowcrm_company_id');
      if (!window.location.pathname.includes('/login')) window.location.href = '/login';
    }
    return Promise.reject(error);
  },
);

export default api;
