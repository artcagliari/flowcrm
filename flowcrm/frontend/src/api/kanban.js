import api from './axios';
export const getKanban = () => api.get('/kanban').then((r) => r.data.data);
export const moveLead = (leadId, lead_stage_id) => api.patch(`/kanban/leads/${leadId}/move`, { lead_stage_id }).then((r) => r.data.data);
