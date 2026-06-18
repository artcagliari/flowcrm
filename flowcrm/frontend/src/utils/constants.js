export const STATUS_BADGE = {
  encaminhado: 'info',
  em_atendimento: 'success',
  aguardando_retorno: 'warning',
  agendado: 'info',
  ativo: 'success',
  pausado: 'warning',
  encerrado: 'danger',
  arquivado: 'danger',
  descartado: 'danger',
  novo: 'info',
  em_conversa: 'warning',
  pago: 'success',
  concluida: 'success',
  concluido: 'success',
  pendente: 'warning',
  atrasada: 'danger',
  atrasado: 'danger',
  cancelado: 'danger',
  nao_compareceu: 'danger',
};

/** Status do cliente — fluxo comercial da empresa */
export const clientStatusOptions = [
  { value: 'encaminhado', label: 'Encaminhado' },
  { value: 'ativo', label: 'Ativo' },
  { value: 'em_atendimento', label: 'Em atendimento' },
  { value: 'aguardando_retorno', label: 'Aguardando retorno' },
  { value: 'agendado', label: 'Agendado' },
  { value: 'pausado', label: 'Pausado' },
  { value: 'encerrado', label: 'Encerrado' },
  { value: 'arquivado', label: 'Arquivado' },
];

/** Contato = primeiro contato simples, sem funil */
export const contactStatusOptions = [
  { value: 'novo', label: 'Novo' },
  { value: 'em_conversa', label: 'Em conversa' },
  { value: 'encaminhado', label: 'Encaminhado' },
  { value: 'descartado', label: 'Descartado' },
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
  { value: 'consulta', label: 'Consulta comercial' },
  { value: 'demo', label: 'Demonstracao' },
  { value: 'visita', label: 'Visita' },
  { value: 'retorno', label: 'Retorno' },
  { value: 'atendimento online', label: 'Atendimento online' },
];

export const financialStatusOptions = [
  { value: 'pago', label: 'Pago' },
  { value: 'pendente', label: 'Pendente' },
  { value: 'atrasado', label: 'Atrasado' },
];

export const originOptions = [
  { value: 'WhatsApp', label: 'WhatsApp' },
  { value: 'Google', label: 'Google' },
  { value: 'Instagram', label: 'Instagram' },
  { value: 'Indicacao', label: 'Indicacao' },
  { value: 'Site', label: 'Site' },
];

export const paymentMethodOptions = [
  { value: 'Pix', label: 'Pix' },
  { value: 'cartao', label: 'Cartao' },
  { value: 'boleto', label: 'Boleto' },
  { value: 'dinheiro', label: 'Dinheiro' },
  { value: 'transferencia', label: 'Transferencia' },
];
