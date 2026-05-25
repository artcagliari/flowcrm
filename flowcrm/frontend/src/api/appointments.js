import api from './axios';
import { resource } from './resource';
const appointments = resource('/appointments');
export const cancelAppointment = (id) => api.patch(`/appointments/${id}/cancel`).then((r) => r.data.data);
export const completeAppointment = (id) => api.patch(`/appointments/${id}/complete`).then((r) => r.data.data);
export default appointments;
