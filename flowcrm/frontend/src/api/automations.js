import { resource } from './resource';

const automations = resource('/automations');
const templates = resource('/message-templates');
const sequences = resource('/sequences');
const tags = resource('/tags');
const lossReasons = resource('/loss-reasons');

export const listAutomations = automations.list;
export const createAutomation = automations.create;
export const updateAutomation = automations.update;
export const removeAutomation = automations.remove;

export const listTemplates = templates.list;
export const createTemplate = templates.create;
export const updateTemplate = templates.update;
export const removeTemplate = templates.remove;

export const listSequences = sequences.list;
export const createSequence = sequences.create;
export const updateSequence = sequences.update;
export const removeSequence = sequences.remove;

export const listTags = tags.list;
export const createTag = tags.create;
export const updateTag = tags.update;
export const removeTag = tags.remove;

export const listLossReasons = lossReasons.list;
export const createLossReason = lossReasons.create;
export const updateLossReason = lossReasons.update;
export const removeLossReason = lossReasons.remove;
