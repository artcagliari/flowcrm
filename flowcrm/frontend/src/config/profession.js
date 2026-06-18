import {
  BarChart3,
  Bell,
  Briefcase,
  CalendarDays,
  FolderOpen,
  GitBranch,
  LayoutDashboard,
  ListChecks,
  MessageCircle,
  Plug,
  Settings,
  ShieldCheck,
  Target,
  UserPlus,
  Users,
  Wallet,
} from 'lucide-react';

const sharedTail = [
  ['/tasks', 'Tarefas', ListChecks],
  ['/appointments', 'Agenda', CalendarDays],
  ['/documents', 'Documentos', FolderOpen],
  ['/finance', 'Financeiro', Wallet],
  ['/reports', 'Relatorios', BarChart3],
  ['/notifications', 'Notificacoes', Bell],
  ['/users', 'Usuarios', ShieldCheck],
  ['/integrations', 'Integracoes', Plug],
  ['/settings', 'Configuracoes', Settings],
];

export const professionConfig = {
  empresa: {
    label: 'Empresa',
    workspace: 'Comercial',
    accent: 'from-blue-500 to-indigo-400',
    icon: Briefcase,
    clientsLabel: 'Clientes',
    leadsLabel: 'Leads',
    leadsSubtitle: 'Oportunidades em prospeccao. Qualifique e converta em clientes.',
    pipelineLabel: 'Pipeline de vendas',
    pipelineSubtitle: 'Etapas do funil comercial da sua equipe.',
    dashboardSubtitle: 'Visao comercial: leads, pipeline, forecast e proximas acoes.',
    leadFields: [
      { name: 'name', label: 'Nome' },
      { name: 'phone', label: 'Telefone' },
      { name: 'whatsapp', label: 'WhatsApp' },
      { name: 'email', label: 'E-mail' },
      { name: 'origin', label: 'Origem' },
      { name: 'interest', label: 'Interesse / necessidade' },
    ],
    nav: [
      ['/dashboard', 'Dashboard', LayoutDashboard],
      ['/leads', 'Leads', UserPlus],
      ['/clients', 'Clientes', Users],
      ['/deals', 'Oportunidades', Target],
      ['/pipeline', 'Pipeline', GitBranch],
      ['/whatsapp', 'WhatsApp', MessageCircle],
      ...sharedTail,
    ],
  },
};

export function getProfessionConfig() {
  return professionConfig.empresa;
}
