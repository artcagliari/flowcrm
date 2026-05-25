export function formatDate(value) {
  if (!value) return '-';
  return new Intl.DateTimeFormat('pt-BR').format(new Date(value));
}
