import { Building2, Clock, PauseCircle, Users } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getAdminDashboard } from '../../api/admin';
import PageHeader from '../../components/shared/PageHeader';
import StatCard from '../../components/dashboard/StatCard';
import Card from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';

export default function AdminDashboard() {
  const [data, setData] = useState(null);
  useEffect(() => { getAdminDashboard().then(setData); }, []);
  const stats = data?.stats || {};

  return (
    <>
      <PageHeader title="Visao geral" subtitle="Metricas gerais da plataforma e ultimas empresas cadastradas." />
      <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <StatCard icon={Building2} label="Empresas" value={stats.companies || 0} trend={`${stats.active_companies || 0} ativas`} />
        <StatCard icon={PauseCircle} label="Suspensas" value={stats.suspended_companies || 0} trend={`${stats.inactive_companies || 0} inativas`} />
        <StatCard icon={Clock} label="Vencendo" value={stats.expiring_companies || 0} trend="30 dias" />
        <StatCard icon={Users} label="Usuarios" value={stats.company_users || 0} trend="Empresas cliente" />
      </section>
      <Card className="mt-4">
        <h2 className="mb-3 text-base font-bold">Ultimas empresas cadastradas</h2>
        {(data?.latest_companies || []).length ? (
          <div className="grid gap-2">
            {data.latest_companies.map((company) => (
              <Link to={`/admin/companies/${company.id}`} key={company.id} className="rounded-2xl border border-white/10 bg-white/5 p-3 text-sm hover:bg-white/10">
                <strong>{company.name}</strong>
                <p className="text-slate-400">{company.status} · {company.users_count || 0} usuarios</p>
              </Link>
            ))}
          </div>
        ) : <EmptyState title="Nenhuma empresa cadastrada" description="Crie a primeira empresa cliente pelo painel master." />}
      </Card>
    </>
  );
}
