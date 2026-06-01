export const STATUS_BADGE = {
  ativo: 'success',
  pago: 'success',
  concluida: 'success',
  concluido: 'success',
  convertido: 'success',
  quente: 'success',
  agendado: 'info',
  confirmado: 'info',
  pendente: 'warning',
  morno: 'warning',
  atrasada: 'danger',
  atrasado: 'danger',
  frio: 'danger',
  perdido: 'danger',
  cancelado: 'danger',
  nao_compareceu: 'danger',
};

export const leadStages = ['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociacao', 'Fechado', 'Perdido'];

export const clientStatusOptions = [
  { value: 'ativo', label: 'Ativo' },
  { value: 'inativo', label: 'Inativo' },
  { value: 'em atendimento', label: 'Em atendimento' },
  { value: 'aguardando retorno', label: 'Aguardando retorno' },
  { value: 'perdido', label: 'Perdido' },
  { value: 'arquivado', label: 'Arquivado' },
];

export const leadStatusOptions = [
  { value: 'novo', label: 'Novo' },
  { value: 'contatado', label: 'Contatado' },
  { value: 'qualificado', label: 'Qualificado' },
  { value: 'proposta_enviada', label: 'Proposta enviada' },
  { value: 'negociacao', label: 'Negociacao' },
  { value: 'convertido', label: 'Convertido' },
  { value: 'perdido', label: 'Perdido' },
];

export const leadTemperatureOptions = [
  { value: 'frio', label: 'Frio' },
  { value: 'morno', label: 'Morno' },
  { value: 'quente', label: 'Quente' },
];

export const taskStatusOptions = [
  { value: 'pendente', label: 'Pendente' },
  { value: 'em andamento', label: 'Em andamento' },
  { value: 'concluida', label: 'Concluida' },
  { value: 'atrasada', label: 'Atrasada' },
];

export const taskPriorityOptions = [
  { value: 'baixa', label: 'Baixa' },
  { value: 'media', label: 'Media' },
  { value: 'alta', label: 'Alta' },
  { value: 'urgente', label: 'Urgente' },
];

export const appointmentStatusOptions = [
  { value: 'agendado', label: 'Agendado' },
  { value: 'confirmado', label: 'Confirmado' },
  { value: 'concluido', label: 'Concluido' },
  { value: 'cancelado', label: 'Cancelado' },
  { value: 'nao_compareceu', label: 'Nao compareceu' },
];

export const appointmentTypeOptions = [
  { value: 'reuniao', label: 'Reuniao' },
  { value: 'consulta', label: 'Consulta' },
  { value: 'visita', label: 'Visita' },
  { value: 'ligacao', label: 'Ligacao' },
  { value: 'audiencia', label: 'Audiencia' },
  { value: 'retorno', label: 'Retorno' },
  { value: 'atendimento online', label: 'Atendimento online' },
];

export const financialStatusOptions = [
  { value: 'pago', label: 'Pago' },
  { value: 'pendente', label: 'Pendente' },
  { value: 'atrasado', label: 'Atrasado' },
];

export const originOptions = [
  { value: 'Google', label: 'Google' },
  { value: 'Instagram', label: 'Instagram' },
  { value: 'Indicacao', label: 'Indicacao' },
  { value: 'Landing Page', label: 'Landing Page' },
  { value: 'Evento', label: 'Evento' },
];

export const paymentMethodOptions = [
  { value: 'Pix', label: 'Pix' },
  { value: 'cartao', label: 'Cartao' },
  { value: 'boleto', label: 'Boleto' },
  { value: 'dinheiro', label: 'Dinheiro' },
  { value: 'transferencia', label: 'Transferencia' },
];
