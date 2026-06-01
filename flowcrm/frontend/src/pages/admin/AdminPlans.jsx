import { useEffect, useState } from 'react';
import { listAdminPlans } from '../../api/admin';
import PageHeader from '../../components/shared/PageHeader';
import Card from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { formatCurrency } from '../../utils/formatCurrency';

export default function AdminPlans() {
  const [plans, setPlans] = useState([]);
  useEffect(() => { listAdminPlans().then(setPlans); }, []);
  return (
    <>
      <PageHeader title="Planos" subtitle="Gerencie os planos comerciais disponiveis para empresas clientes." />
      <Card>
        {plans.length ? <div className="grid gap-3">{plans.map((plan) => <div className="rounded-2xl border border-white/10 bg-white/5 p-3" key={plan.id}><strong>{plan.name}</strong><p className="text-sm text-slate-400">{formatCurrency(plan.monthly_price)} · {plan.max_users || 'Ilimitado'} usuarios</p></div>)}</div> : <EmptyState title="Nenhum plano cadastrado" description="Cadastre planos via API administrativa." />}
      </Card>
    </>
  );
}
