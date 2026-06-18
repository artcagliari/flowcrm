import { resource } from './resource';
import api from './axios';

const deals = resource('/deals');

export const listDeals = deals.list;
export const getDeal = deals.get;
export const createDeal = deals.create;
export const updateDeal = deals.update;
export const removeDeal = deals.remove;
export const winDeal = (id) => api.patch(`/deals/${id}/won`).then((r) => r.data.data);
export const loseDeal = (id, payload) => api.patch(`/deals/${id}/lost`, payload).then((r) => r.data.data);
