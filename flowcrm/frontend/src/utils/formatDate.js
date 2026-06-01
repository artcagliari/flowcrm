const locale = 'pt-BR';
const timeZone = 'America/Sao_Paulo';

function toDate(value) {
  if (!value) return null;
  if (value instanceof Date) return value;
  return new Date(String(value).replace(' ', 'T'));
}

export function formatDate(value) {
  const date = toDate(value);
  if (!date || Number.isNaN(date.getTime())) return '-';
  return new Intl.DateTimeFormat(locale, { timeZone }).format(date);
}

export function formatTime(value) {
  const date = toDate(value);
  if (!date || Number.isNaN(date.getTime())) return '-';
  return new Intl.DateTimeFormat(locale, { hour: '2-digit', minute: '2-digit', timeZone }).format(date);
}

export function formatDateTime(value) {
  const date = toDate(value);
  if (!date || Number.isNaN(date.getTime())) return '-';
  return `${formatDate(date)} ${formatTime(date)}`;
}

export function toApiDateTime(date, time) {
  if (!date || !time) return '';
  return `${date} ${time}:00`;
}

export function fromApiDateTime(value) {
  const date = toDate(value);
  if (!date || Number.isNaN(date.getTime())) return { date: '', time: '' };
  const parts = new Intl.DateTimeFormat('en-CA', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone,
  }).formatToParts(date).reduce((acc, part) => ({ ...acc, [part.type]: part.value }), {});

  return {
    date: `${parts.year}-${parts.month}-${parts.day}`,
    time: `${parts.hour}:${parts.minute}`,
  };
}
