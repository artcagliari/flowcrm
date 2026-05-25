import { Area, AreaChart, Bar, BarChart, CartesianGrid, Tooltip, XAxis } from 'recharts';
import { CalendarDays, ListChecks, Target, TrendingUp, Users, Wallet } from 'lucide-react';
import ChartCard from '../components/dashboard/ChartCard';
import StatCard from '../components/dashboard/StatCard';
import Card from '../components/ui/Card';
import Skeleton from '../components/ui/Skeleton';
import PageHeader from '../components/shared/PageHeader';
import { useDashboard } from '../hooks/useDashboard';
import { formatCurrency } from '../utils/formatCurrency';

export default function Dashboard() {
  const { data, loading } = useDashboard();
  if (loading) return <div className="grid gap-4 lg:grid-cols-4"><Skeleton /><Skeleton /><Skeleton /><Skeleton /></div>;
  const stats = data?.stats || {};
  return <><PageHeader title="Dashboard executivo" subtitle="Visão consolidada de vendas, agenda, clientes e financeiro." /><section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4"><StatCard icon={Users} label="Clientes" value={stats.clients || 0} trend="+12%" /><StatCard icon={Target} label="Leads novos" value={stats.new_leads || 0} trend="+8%" /><StatCard icon={ListChecks} label="Tarefas pendentes" value={stats.pending_tasks || 0} trend="+4" /><StatCard icon={Wallet} label="Receita do mês" value={formatCurrency(stats.monthly_revenue)} trend="+18%" /><StatCard icon={TrendingUp} label="Lucro estimado" value={formatCurrency(stats.estimated_profit)} trend="+9%" /><StatCard icon={CalendarDays} label="Compromissos hoje" value={stats.today_appointments || 0} trend="Hoje" /></section><section className="mt-4 grid gap-4 xl:grid-cols-[1.4fr_.8fr]"><ChartCard title="Receita mensal"><AreaChart data={data.monthly_revenue_chart || []}><CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,.08)" /><XAxis dataKey="month" stroke="#94A3B8" /><Tooltip /><Area dataKey="value" stroke="#7DD3FC" fill="#4F8CFF" fillOpacity={0.24} /></AreaChart></ChartCard><ChartCard title="Leads por origem"><BarChart data={data.leads_by_origin || []}><XAxis dataKey="name" stroke="#94A3B8" /><Tooltip /><Bar dataKey="value" fill="#7DD3FC" radius={[8,8,0,0]} /></BarChart></ChartCard></section><Card className="mt-4"><h2 className="mb-3 text-lg font-bold">Atividades recentes</h2><div className="grid gap-2">{(data.recent_activities || []).map((a) => <div className="rounded-2xl border border-white/10 bg-white/5 p-3" key={a.id}>{a.description}</div>)}</div></Card></>;
}
