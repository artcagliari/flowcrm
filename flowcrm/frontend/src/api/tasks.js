import api from './axios';
import { resource } from './resource';
const tasks = resource('/tasks');
export const completeTask = (id) => api.patch(`/tasks/${id}/complete`).then((r) => r.data.data);
export default tasks;
