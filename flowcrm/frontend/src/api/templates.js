import api from './axios';

export const listMessageTemplates = (channel) =>
  api.get('/message-templates', { params: channel ? { channel } : {} }).then((r) => r.data.data);
