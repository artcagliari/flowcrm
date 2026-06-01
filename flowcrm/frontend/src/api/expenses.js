import api from './axios';
import { resource } from './resource';
export const markExpensePaid = (id) => api.patch(`/expenses/${id}/paid`).then((r) => r.data.data);
export default resource('/expenses');
