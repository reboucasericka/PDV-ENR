/**
 * Extrai mensagem amigavel a partir de erros Axios/Laravel.
 */
export function friendlyError(error, fallback = 'Ocorreu um erro inesperado.') {
  if (!error) {
    return fallback;
  }

  const data = error.response?.data;
  if (typeof data?.message === 'string' && data.message.trim()) {
    return data.message;
  }

  const firstValidation = Object.values(data?.errors || {})?.[0]?.[0];
  if (firstValidation) {
    return firstValidation;
  }

  if (error.response?.status === 401) {
    return 'Sessao expirada. Faca login novamente.';
  }

  if (error.response?.status === 403) {
    return 'Nao tem permissao para esta acao.';
  }

  if (error.response?.status === 404) {
    return 'Recurso nao encontrado.';
  }

  if (error.response?.status >= 500) {
    return 'Erro no servidor. Tente novamente em instantes.';
  }

  if (error.message === 'Network Error') {
    return 'Sem ligacao ao servidor. Verifique a rede.';
  }

  return fallback;
}
