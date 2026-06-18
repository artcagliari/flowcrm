import { useEffect, useState } from 'react';
import { listSalesGoals, saveSalesGoal } from '../api/advanced';
import { listUsers } from '../api/users';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
import { formatCurrency } from '../utils/formatCurrency';

export default function SalesGoals() {
  const [goals, setGoals] = useState([]);
  const [users, setUsers] = useState([]);
  const [form, setForm] = useState({ user_id: '', target_amount: '', target_deals: '' });

  const load = () => listSalesGoals().then(setGoals);
  useEffect(() => {
    load();
    listUsers().then((data) => setUsers(data?.users || []));
  }, []);

  const submit = async (e) => {
    e.preventDefault();
    const now = new Date();
    await saveSalesGoal({
      user_id: Number(form.user_id),
      year: now.getFullYear(),
      month: now.getMonth() + 1,
      target_amount: Number(form.target_amount || 0),
      target_deals: Number(form.target_deals || 0),
    });
    load();
  };

  return (
    <>
      <PageHeader title="Metas de vendas" subtitle="Acompanhe metas mensais por vendedor." />
      <Card className="mb-4">
        <form className="grid gap-3 md:grid-cols-4" onSubmit={submit}>
          <Select label="Vendedor" value={form.user_id} onChange={(e) => setForm({ ...form, user_id: e.target.value })} options={users.map((u) => ({ value: u.id, label: u.name }))} required />
          <Input label="Meta R$" type="number" value={form.target_amount} onChange={(e) => setForm({ ...form, target_amount: e.target.value })} />
          <Input label="Meta deals" type="number" value={form.target_deals} onChange={(e) => setForm({ ...form, target_deals: e.target.value })} />
          <Button type="submit" className="self-end">Salvar meta</Button>
        </form>
      </Card>
      <div className="grid gap-4 md:grid-cols-2">
        {goals.map((goal) => (
          <Card key={goal.id}>
            <strong>{goal.user?.name}</strong>
            <p className="mt-2 text-sm text-slate-400">Receita: {formatCurrency(goal.achieved_amount)} / {formatCurrency(goal.target_amount)} ({goal.amount_progress}%)</p>
            <div className="mt-2 h-2 rounded-full bg-white/10"><div className="h-2 rounded-full bg-sky-400" style={{ width: `${Math.min(goal.amount_progress, 100)}%` }} /></div>
            <p className="mt-3 text-sm text-slate-400">Deals: {goal.achieved_deals} / {goal.target_deals} ({goal.deals_progress}%)</p>
          </Card>
        ))}
      </div>
    </>
  );
}
