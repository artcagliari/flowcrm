export function handleApiError(error, fallback = 'Nao foi possivel concluir a operacao.') {
  const response = error?.response;
  const data = response?.data || {};
  const errors = data.errors || {};
  const firstFieldError = Object.values(errors).flat().find(Boolean);

  return {
    message: data.message || firstFieldError || fallback,
    errors,
    status: response?.status,
  };
}
