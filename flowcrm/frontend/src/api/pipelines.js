import { resource } from './resource';
import api from './axios';

const pipelines = resource('/pipelines');

export const listPipelines = pipelines.list;
export const createPipeline = pipelines.create;
export const updatePipeline = pipelines.update;
export const removePipeline = pipelines.remove;
export const listStages = () => api.get('/lead-stages').then((r) => r.data.data);
export const createStage = (payload) => api.post('/lead-stages', payload).then((r) => r.data.data);
export const updateStage = (id, payload) => api.put(`/lead-stages/${id}`, payload).then((r) => r.data.data);
export const removeStage = (id) => api.delete(`/lead-stages/${id}`).then((r) => r.data.data);
export const reorderStages = (stages) => api.put('/lead-stages/reorder', { stages }).then((r) => r.data.data);
