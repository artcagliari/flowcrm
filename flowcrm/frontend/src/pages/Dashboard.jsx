import { Area, AreaChart, Bar, BarChart, CartesianGrid, Tooltip, XAxis } from 'recharts';
import { AlertCircle, CalendarDays, CheckCircle2, ListChecks, Sparkles, UserPlus, Users, Wallet } from 'lucide-react';
import ChartCard from '../components/dashboard/ChartCard';
import StatCard from '../components/dashboard/StatCard';
import Card from '../components/ui/Card';
import Skeleton from '../components/ui/Skeleton';
import PageHeader from '../components/shared/PageHeader';
import { useDashboard } from '../hooks/useDashboard';
import useProfessionMode from '../hooks/useProfessionMode';
import { formatCurrency } from '../utils/formatCurrency';
import { formatDate, formatDateTime } from '../utils/formatDate';

export default function Dashboard() {
  const { config } = useProfessionMode();
  const { data, loading } = useDashboard();
  if (loading) return <div className="grid gap-4 lg:grid-cols-4"><Skeleton /><Skeleton /><Skeleton /><Skeleton /></div>;

  const stats = data?.stats || {};

  return (
    <>
      <PageHeader title="Dashboard" subtitle={config.dashboardSubtitle} />
      <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <StatCard icon={Users} label={`${config.clientsLabel} ativos`} value={stats.active_clients || 0} trend={`${stats.clients || 0} no total`} />
        <StatCard icon={UserPlus} label="Contatos pendentes" value={stats.open_contacts || 0} trend="Aguardando acao" />
        <StatCard
          icon={Sparkles}
          label="Compromissos agendados"
          value={stats.active_cases || 0}
          trend="Proximos na agenda"
        />
        <StatCard icon={CalendarDays} label="Compromissos hoje" value={stats.today_appointments || 0} trend="Agenda do dia" />
        <StatCard icon={ListChecks} label="Tarefas pendentes" value={stats.pending_tasks || 0} trend={`${stats.late_tasks || 0} atrasadas`} />
        <StatCard icon={CheckCircle2} label="Receitas do mes" value={formatCurrency(stats.monthly_revenue)} trend="Recebido" />
        <StatCard icon={Wallet} label="Pagamentos pendentes" value={stats.pending_payments || 0} trend={`${stats.late_payments || 0} atrasados`} />
        <StatCard icon={AlertCircle} label="Resultado do mes" value={formatCurrency(stats.estimated_profit)} trend="Receita - despesas" />
      </section>
      <Card className="mt-4">
        <DashboardList
          title="Leads recentes"
          items={data.pending_contacts}
          empty="Nenhum lead pendente."
          render={(item) => `${item.name} — ${item.status || 'novo'} — ${item.phone || item.whatsapp || 'Sem telefone'}`}
        />
      </Card>
      <section className="mt-4 grid gap-4 xl:grid-cols-[1.4fr_.8fr]">
        <ChartCard title="Receita mensal">
          <AreaChart data={data.monthly_revenue_chart || []}>
            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,.08)" />
            <XAxis dataKey="month" stroke="#94A3B8" />
            <Tooltip />
            <Area dataKey="value" stroke="#7DD3FC" fill="#4F8CFF" fillOpacity={0.24} />
          </AreaChart>
        </ChartCard>
        <ChartCard title={`${config.clientsLabel} por status`}>
          <BarChart data={data.clients_by_status || []}>
            <XAxis dataKey="name" stroke="#94A3B8" />
            <Tooltip />
            <Bar dataKey="value" fill="#7DD3FC" radius={[8, 8, 0, 0]} />
          </BarChart>
        </ChartCard>
      </section>
      <Card className="mt-4">
        <DashboardList title="Proximos compromissos" items={data.upcoming_appointments} empty="Nenhum compromisso futuro." render={(item) => `${item.title} - ${formatDateTime(item.start_at || item.starts_at)}`} />
      </Card>
      <section className="mt-4 grid gap-4 xl:grid-cols-3">
        <Card><DashboardList title="Tarefas urgentes" items={data.urgent_tasks} empty="Nenhuma tarefa urgente." render={(item) => `${item.title} - ${formatDate(item.due_date || item.due_at)}`} /></Card>
        <Card><DashboardList title="Ultimas atividades" items={data.recent_activities} empty="Nenhuma atividade registrada." render={(item) => item.description} /></Card>
        <Card><DashboardList title="Pagamentos recentes" items={data.recent_payments} empty="Nenhum pagamento cadastrado." render={(item) => `${item.description} - ${formatCurrency(item.amount)}`} /></Card>
      </section>
    </>
  );
}

function DashboardList({ title, items = [], empty, render }) {
  return (
    <div>
      <h2 className="mb-3 text-base font-bold">{title}</h2>
      <div className="grid gap-2">
        {items.map((item) => <div className="rounded-2xl border border-white/10 bg-white/5 p-3 text-sm text-slate-200" key={item.id}>{render(item)}</div>)}
        {items.length === 0 && <p className="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-400">{empty}</p>}
      </div>
    </div>
  );
}
