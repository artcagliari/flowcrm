export const STATUS_BADGE = {
  ativo: 'success',
  pago: 'success',
  concluida: 'success',
  'concluída': 'success',
  quente: 'success',
  pendente: 'warning',
  morno: 'warning',
  atrasada: 'danger',
  atrasado: 'danger',
  frio: 'danger',
  perdido: 'danger',
};

export const leadStages = ['Novo lead', 'Primeiro contato', 'Qualificado', 'Proposta enviada', 'Negociação', 'Fechado', 'Perdido'];

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
  { value: 'proposta enviada', label: 'Proposta enviada' },
  { value: 'negociação', label: 'Negociação' },
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
  { value: 'concluída', label: 'Concluída' },
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
  { value: 'concluído', label: 'Concluído' },
  { value: 'cancelado', label: 'Cancelado' },
  { value: 'não compareceu', label: 'Não compareceu' },
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
  { value: 'Indicação', label: 'Indicação' },
  { value: 'Landing Page', label: 'Landing Page' },
  { value: 'Evento', label: 'Evento' },
];

export const paymentMethodOptions = [
  { value: 'Pix', label: 'Pix' },
  { value: 'cartão', label: 'Cartão' },
  { value: 'boleto', label: 'Boleto' },
  { value: 'dinheiro', label: 'Dinheiro' },
  { value: 'transferência', label: 'Transferência' },
];
