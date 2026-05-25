import api from './axios';
import { resource } from './resource';
const payments = resource('/payments');
export const markPaymentPaid = (id) => api.patch(`/payments/${id}/paid`).then((r) => r.data.data);
export default payments;
