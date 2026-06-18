import {
  Activity,
  BarChart3,
  BriefcaseBusiness,
  CalendarDays,
  CheckCircle2,
  Download,
  Filter,
  PieChart as PieChartIcon,
  RefreshCw,
  TrendingUp,
  Users,
  Wallet,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Line,
  LineChart,
  Pie,
  PieChart,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { getReports } from '../api/reports';
import ChartCard from '../components/dashboard/ChartCard';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
import useProfessionMode from '../hooks/useProfessionMode';
import { formatCurrency } from '../utils/formatCurrency';

const colors = ['#4F8CFF', '#7DD3FC', '#22C55E', '#FACC15', '#EF4444', '#A78BFA', '#38BDF8', '#94A3B8'];

const sections = [
  { id: 'overview', label: 'Visao geral', icon: Activity },
  { id: 'finance', label: 'Gastos e receita', icon: Wallet },
  { id: 'appointments', label: 'Agendamentos', icon: CalendarDays },
  { id: 'clients', label: 'Clientes', icon: Users },
  { id: 'leads', label: 'Contatos', icon: TrendingUp },
  { id: 'tasks', label: 'Tarefas', icon: CheckCircle2 },
];

const emptyReports = {
  overview: {},
  clients: {},
  leads: {},
  finance: {},
  appointments: {},
  tasks: {},
};

function today(offsetDays = 0) {
  const date = new Date();
  date.setDate(date.getDate() + offsetDays);
  return date.toISOString().slice(0, 10);
}

function chartData(data) {
  return Array.isArray(data) ? data.map((item) => ({ ...item, value: Number(item.value || 0) })) : [];
}

function tooltipFormatter(value, name, props) {
  const key = props?.payload?.currency ? formatCurrency(value) : value;
  return [key, name === 'value' ? 'Total' : name];
}

function flattenSection(sectionData, prefix = '') {
  return Object.entries(sectionData || {}).flatMap(([key, value]) => {
    const path = prefix ? `${prefix}.${key}` : key;
    if (Array.isArray(value)) {
      return value.flatMap((item, index) => flattenSection(item, `${path}.${index + 1}`));
    }
    if (value && typeof value === 'object') return flattenSection(value, path);
    return [{ indicador: path, valor: value ?? '' }];
  });
}

function exportCsv(section, data) {
  const rows = flattenSection(data);
  const csv = [
    'Indicador;Valor',
    ...rows.map((row) => `"${String(row.indicador).replaceAll('"', '""')}";"${String(row.valor).replaceAll('"', '""')}"`),
  ].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `flowcrm-relatorio-${section}.csv`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function SectionButton({ section, active, onClick }) {
  const Icon = section.icon;
  return (
    <button
      type="button"
      onClick={onClick}
      className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl border px-4 text-sm font-semibold transition ${
        active ? 'border-blue-300/50 bg-blue-500/20 text-sky-100 shadow-lg shadow-blue-500/10' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'
      }`}
    >
      <Icon size={16} />
      {section.label}
    </button>
  );
}

function ReportStat({ icon: Icon, label, value, detail, tone = 'blue' }) {
  const tones = {
    blue: 'from-blue-500/20 text-sky-100',
    green: 'from-emerald-500/20 text-emerald-100',
    amber: 'from-amber-500/20 text-amber-100',
    red: 'from-red-500/20 text-red-100',
    slate: 'from-slate-500/20 text-slate-100',
  };

  return (
    <Card className="min-h-36">
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-sm text-slate-400">{label}</p>
          <strong className="mt-2 block text-2xl text-white">{value}</strong>
          {detail && <span className="mt-2 block text-xs text-slate-500">{detail}</span>}
        </div>
        <span className={`grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br ${tones[tone]}`}>
          <Icon size={18} />
        </span>
      </div>
    </Card>
  );
}

function EmptyChart() {
  return <EmptyState title="Sem dados" description="Nao ha informacoes para este periodo." />;
}

function SimpleBarChart({ data, color = '#4F8CFF', currency = false }) {
  const rows = chartData(data).map((item) => ({ ...item, currency }));
  if (!rows.length) return <EmptyChart />;

  return (
    <BarChart data={rows}>
      <CartesianGrid stroke="rgba(255,255,255,.08)" vertical={false} />
      <XAxis dataKey="name" stroke="#94A3B8" tickLine={false} axisLine={false} />
      <YAxis stroke="#94A3B8" tickLine={false} axisLine={false} width={44} />
      <Tooltip formatter={tooltipFormatter} contentStyle={{ background: '#0B1020', border: '1px solid rgba(255,255,255,.12)', borderRadius: 16 }} />
      <Bar dataKey="value" fill={color} radius={[10, 10, 0, 0]} />
    </BarChart>
  );
}

function SimplePieChart({ data }) {
  const rows = chartData(data);
  if (!rows.length) return <EmptyChart />;

  return (
    <PieChart>
      <Tooltip formatter={tooltipFormatter} contentStyle={{ background: '#0B1020', border: '1px solid rgba(255,255,255,.12)', borderRadius: 16 }} />
      <Pie data={rows} dataKey="value" nameKey="name" innerRadius={54} outerRadius={92} paddingAngle={3}>
        {rows.map((entry, index) => <Cell key={entry.name} fill={colors[index % colors.length]} />)}
      </Pie>
    </PieChart>
  );
}

function FinanceTrend({ revenue, expenses }) {
  const merged = new Map();
  chartData(revenue).forEach((item) => merged.set(item.name, { name: item.name, receita: item.value, gastos: 0, currency: true }));
  chartData(expenses).forEach((item) => {
    const current = merged.get(item.name) || { name: item.name, receita: 0, gastos: 0, currency: true };
    merged.set(item.name, { ...current, gastos: item.value });
  });
  const rows = [...merged.values()].sort((a, b) => a.name.localeCompare(b.name));
  if (!rows.length) return <EmptyChart />;

  return (
    <AreaChart data={rows}>
      <defs>
        <linearGradient id="revenueFill" x1="0" y1="0" x2="0" y2="1">
          <stop offset="5%" stopColor="#4F8CFF" stopOpacity={0.35} />
          <stop offset="95%" stopColor="#4F8CFF" stopOpacity={0} />
        </linearGradient>
        <linearGradient id="expenseFill" x1="0" y1="0" x2="0" y2="1">
          <stop offset="5%" stopColor="#EF4444" stopOpacity={0.25} />
          <stop offset="95%" stopColor="#EF4444" stopOpacity={0} />
        </linearGradient>
      </defs>
      <CartesianGrid stroke="rgba(255,255,255,.08)" vertical={false} />
      <XAxis dataKey="name" stroke="#94A3B8" tickLine={false} axisLine={false} />
      <YAxis stroke="#94A3B8" tickLine={false} axisLine={false} width={44} />
      <Tooltip formatter={(value) => formatCurrency(value)} contentStyle={{ background: '#0B1020', border: '1px solid rgba(255,255,255,.12)', borderRadius: 16 }} />
      <Area type="monotone" dataKey="receita" stroke="#4F8CFF" fill="url(#revenueFill)" strokeWidth={3} />
      <Area type="monotone" dataKey="gastos" stroke="#EF4444" fill="url(#expenseFill)" strokeWidth={3} />
    </AreaChart>
  );
}

function MonthlyLine({ data, color = '#7DD3FC' }) {
  const rows = chartData(data);
  if (!rows.length) return <EmptyChart />;

  return (
    <LineChart data={rows}>
      <CartesianGrid stroke="rgba(255,255,255,.08)" vertical={false} />
      <XAxis dataKey="name" stroke="#94A3B8" tickLine={false} axisLine={false} />
      <YAxis stroke="#94A3B8" tickLine={false} axisLine={false} width={44} />
      <Tooltip formatter={tooltipFormatter} contentStyle={{ background: '#0B1020', border: '1px solid rgba(255,255,255,.12)', borderRadius: 16 }} />
      <Line type="monotone" dataKey="value" stroke={color} strokeWidth={3} dot={{ fill: color, strokeWidth: 0, r: 4 }} />
    </LineChart>
  );
}

export default function Reports() {
  const { config } = useProfessionMode();
  const [active, setActive] = useState('overview');
  const [filters, setFilters] = useState({ from: today(-30), to: today() });
  const [reports, setReports] = useState(emptyReports);
  const [loading, setLoading] = useState(true);

  async function loadReports(params = filters) {
    setLoading(true);
    const data = await getReports(params);
    setReports({ ...emptyReports, ...data });
    setLoading(false);
  }

  useEffect(() => {
    loadReports();
  }, []);

  const overview = reports.overview || {};
  const profit = Number(overview.revenue || 0) - Number(overview.expenses || 0);
  const activeSection = useMemo(() => sections.find((section) => section.id === active), [active]);

  return (
    <>
      <PageHeader title="Relatorios" subtitle={`Indicadores do ${config.workspace.toLowerCase()}: financeiro, agenda, ${config.clientsLabel.toLowerCase()} e contatos.`} />

      <Card className="mb-5">
        <div className="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
          <div className="grid gap-3 md:grid-cols-3">
            <Input label="Data inicial" type="date" value={filters.from} onChange={(event) => setFilters({ ...filters, from: event.target.value })} />
            <Input label="Data final" type="date" value={filters.to} onChange={(event) => setFilters({ ...filters, to: event.target.value })} />
            <Select label="Atalho" onChange={(event) => {
              const days = Number(event.target.value);
              const next = { from: today(-days), to: today() };
              setFilters(next);
              loadReports(next);
            }}>
              <option value="30">Ultimos 30 dias</option>
              <option value="7">Ultimos 7 dias</option>
              <option value="90">Ultimos 90 dias</option>
              <option value="365">Ultimos 12 meses</option>
            </Select>
          </div>
          <div className="flex flex-wrap gap-2">
            <Button variant="secondary" onClick={() => loadReports()}><Filter size={16} /> Aplicar filtros</Button>
            <Button variant="secondary" onClick={() => loadReports()}><RefreshCw size={16} /> Atualizar</Button>
            <Button onClick={() => exportCsv(active, active === 'overview' ? reports : reports[active])}><Download size={16} /> Exportar</Button>
          </div>
        </div>
      </Card>

      <div className="mb-5 flex gap-2 overflow-x-auto pb-2">
        {sections.map((section) => <SectionButton key={section.id} section={section} active={active === section.id} onClick={() => setActive(section.id)} />)}
      </div>

      {loading ? (
        <EmptyState title="Carregando relatorios..." />
      ) : (
        <>
          <div className="mb-4 flex items-center gap-2 text-sm text-slate-400">
            {activeSection?.icon && <activeSection.icon size={16} />}
            <span>{activeSection?.label}</span>
          </div>

          {active === 'overview' && (
            <div className="grid gap-4">
              <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <ReportStat icon={Users} label={`${config.clientsLabel} no periodo`} value={overview.clients || 0} detail="Novos registros filtrados" />
                <ReportStat icon={TrendingUp} label="Contatos no periodo" value={overview.leads || 0} detail="Primeiros contatos recebidos" />
                <ReportStat icon={Wallet} label="Lucro estimado" value={formatCurrency(profit)} detail="Receita paga menos gastos pagos" tone={profit >= 0 ? 'green' : 'red'} />
                <ReportStat icon={CalendarDays} label="Agendamentos" value={overview.appointments || 0} detail="Compromissos do periodo" tone="amber" />
              </div>
              <div className="grid gap-4 xl:grid-cols-2">
                <ChartCard title="Receita x gastos"><FinanceTrend revenue={reports.finance?.monthly_revenue} expenses={reports.finance?.monthly_expenses} /></ChartCard>
                <ChartCard title="Contatos por origem"><SimpleBarChart data={reports.leads?.by_origin} color="#7DD3FC" /></ChartCard>
              </div>
            </div>
          )}

          {active === 'finance' && (
            <div className="grid gap-4">
              <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <ReportStat icon={Wallet} label="Receita paga" value={formatCurrency(overview.revenue)} tone="green" />
                <ReportStat icon={BriefcaseBusiness} label="Gastos pagos" value={formatCurrency(overview.expenses)} tone="red" />
                <ReportStat icon={BarChart3} label="Pagamentos pendentes" value={formatCurrency(reports.finance?.pending_payments)} tone="amber" />
                <ReportStat icon={PieChartIcon} label="Pagamentos atrasados" value={formatCurrency(reports.finance?.overdue_payments)} tone="red" />
              </div>
              <div className="grid gap-4 xl:grid-cols-2">
                <ChartCard title="Receita e gastos por mes"><FinanceTrend revenue={reports.finance?.monthly_revenue} expenses={reports.finance?.monthly_expenses} /></ChartCard>
                <ChartCard title="Gastos por categoria"><SimpleBarChart data={reports.finance?.expenses_by_category} color="#EF4444" currency /></ChartCard>
                <ChartCard title="Status dos pagamentos"><SimplePieChart data={reports.finance?.payments_by_status} /></ChartCard>
              </div>
            </div>
          )}

          {active === 'appointments' && (
            <div className="grid gap-4 xl:grid-cols-[1.2fr_.8fr]">
              <div className="grid gap-4">
                <ChartCard title="Agendamentos por mes"><MonthlyLine data={reports.appointments?.by_month} /></ChartCard>
                <div className="grid gap-4 md:grid-cols-2">
                  <ChartCard title="Por status"><SimplePieChart data={reports.appointments?.by_status} /></ChartCard>
                  <ChartCard title="Por tipo"><SimpleBarChart data={reports.appointments?.by_type} color="#A78BFA" /></ChartCard>
                </div>
              </div>
              <Card>
                <h2 className="mb-4 text-lg font-bold">Proximos agendamentos</h2>
                <div className="grid gap-3">
                  {(reports.appointments?.upcoming || []).length ? reports.appointments.upcoming.map((item) => (
                    <div key={item.id} className="rounded-2xl border border-white/10 bg-white/5 p-3">
                      <strong>{item.title}</strong>
                      <p className="text-sm text-slate-400">{item.type} - {item.status}</p>
                      <span className="text-xs text-slate-500">{new Date(item.starts_at).toLocaleString('pt-BR')}</span>
                    </div>
                  )) : <EmptyState title="Sem proximos agendamentos" />}
                </div>
              </Card>
            </div>
          )}

          {active === 'clients' && (
            <div className="grid gap-4 xl:grid-cols-2">
              <ChartCard title={`${config.clientsLabel} por status`}><SimplePieChart data={reports.clients?.by_status} /></ChartCard>
              <ChartCard title={`${config.clientsLabel} por origem`}><SimpleBarChart data={reports.clients?.by_origin} /></ChartCard>
              <ChartCard title={`Novos ${config.clientsLabel.toLowerCase()} por mes`}><MonthlyLine data={reports.clients?.new_by_month} color="#22C55E" /></ChartCard>
              <ChartCard title="Cidades"><SimpleBarChart data={reports.clients?.by_city} color="#7DD3FC" /></ChartCard>
            </div>
          )}

          {active === 'leads' && (
            <div className="grid gap-4">
              <div className="grid gap-4 md:grid-cols-2">
                <ReportStat icon={TrendingUp} label="Taxa de conversao" value={`${reports.leads?.conversion_rate || 0}%`} detail="Contatos que viraram clientes" tone="green" />
                <ReportStat icon={BarChart3} label="Total de contatos" value={overview.leads || 0} detail="Dentro do periodo filtrado" />
              </div>
              <div className="grid gap-4 xl:grid-cols-2">
                <ChartCard title="Contatos por status"><SimpleBarChart data={reports.leads?.by_status} /></ChartCard>
                <ChartCard title="Contatos por origem"><SimpleBarChart data={reports.leads?.by_origin} color="#7DD3FC" /></ChartCard>
                <ChartCard title="Contatos por etapa"><SimpleBarChart data={reports.leads?.by_stage} color="#A78BFA" /></ChartCard>
              </div>
            </div>
          )}

          {active === 'tasks' && (
            <div className="grid gap-4">
              <div className="grid gap-4 md:grid-cols-3">
                <ReportStat icon={CheckCircle2} label="Tarefas pendentes" value={overview.pending_tasks || 0} tone="amber" />
                <ReportStat icon={Activity} label="Tarefas atrasadas" value={reports.tasks?.overdue || 0} tone="red" />
                <ReportStat icon={BarChart3} label="Concluidas por mes" value={chartData(reports.tasks?.completed_by_month).reduce((sum, item) => sum + item.value, 0)} tone="green" />
              </div>
              <div className="grid gap-4 xl:grid-cols-2">
                <ChartCard title="Tarefas por status"><SimplePieChart data={reports.tasks?.by_status} /></ChartCard>
                <ChartCard title="Tarefas por prioridade"><SimpleBarChart data={reports.tasks?.by_priority} color="#EF4444" /></ChartCard>
                <ChartCard title="Concluidas por mes"><MonthlyLine data={reports.tasks?.completed_by_month} color="#22C55E" /></ChartCard>
              </div>
            </div>
          )}
        </>
      )}
    </>
  );
}
