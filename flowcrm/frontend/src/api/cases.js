import { resource } from './resource';

const cases = resource('/cases');

export const listCases = cases.list;
export const getCase = cases.get;
export const createCase = cases.create;
export const updateCase = cases.update;
export const removeCase = cases.remove;
