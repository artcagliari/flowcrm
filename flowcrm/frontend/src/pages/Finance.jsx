import { CheckCircle2, Plus } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import expensesApi, { markExpensePaid } from '../api/expenses';
import paymentsApi, { markPaymentPaid } from '../api/payments';
import PageHeader from '../components/shared/PageHeader';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import Select from '../components/ui/Select';
import Table from '../components/ui/Table';
import EntityAutocomplete from '../components/shared/EntityAutocomplete';
import { financialStatusOptions, paymentMethodOptions } from '../utils/constants';
import { formatCurrency } from '../utils/formatCurrency';
import { formatDate } from '../utils/formatDate';
import { handleApiError } from '../utils/handleApiError';

const tabs = ['Resumo', 'Receitas', 'Despesas', 'Pendentes', 'Atrasados'];

export default function Finance() {
  const [active, setActive] = useState('Resumo');
  const [payments, setPayments] = useState([]);
  const [expenses, setExpenses] = useState([]);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [modal, setModal] = useState(null);
  const [form, setForm] = useState({ description: '', amount: '', due_date: '', status: 'pendente', payment_method: '' });
  const [client, setClient] = useState(null);

  async function load() {
    const [paymentsData, expensesData] = await Promise.all([paymentsApi.list({ per_page: 100 }), expensesApi.list({ per_page: 100 })]);
    setPayments(paymentsData.data || paymentsData);
    setExpenses(expensesData.data || expensesData);
  }

  useEffect(() => { load(); }, []);

  const summary = useMemo(() => {
    const paidPayments = payments.filter((item) => item.status === 'pago');
    const paidExpenses = expenses.filter((item) => item.status === 'pago');
    const revenue = paidPayments.reduce((sum, item) => sum + Number(item.amount || 0), 0);
    const costs = paidExpenses.reduce((sum, item) => sum + Number(item.amount || 0), 0);
    return {
      revenue,
      costs,
      profit: revenue - costs,
      pending: payments.filter((item) => item.status === 'pendente').length,
      late: payments.filter((item) => item.status === 'atrasado').length,
      received: revenue,
    };
  }, [payments, expenses]);

  function open(type) {
    setModal(type);
    setClient(null);
    setForm({ description: '', amount: '', due_date: '', status: 'pendente', payment_method: '' });
  }

  async function save(event) {
    event.preventDefault();
    setError('');
    try {
      if (modal === 'payment') await paymentsApi.create({ ...form, client_id: client?.id ?? null });
      else await expensesApi.create(form);
      setModal(null);
      setMessage('Operacao concluida.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel salvar.').message);
    }
  }

  async function markPaid(type, id) {
    setError('');
    try {
      if (type === 'payment') await markPaymentPaid(id);
      else await markExpensePaid(id);
      setMessage('Operacao concluida.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel marcar como pago.').message);
    }
  }

  const visiblePayments = active === 'Pendentes' ? payments.filter((item) => item.status === 'pendente') : active === 'Atrasados' ? payments.filter((item) => item.status === 'atrasado') : payments;
  const visibleExpenses = active === 'Pendentes' ? expenses.filter((item) => item.status === 'pendente') : active === 'Atrasados' ? expenses.filter((item) => item.status === 'atrasado') : expenses;

  return (
    <>
      <PageHeader title="Financeiro" subtitle="Acompanhe receitas, despesas, valores pendentes e atrasados.">
        <Button onClick={() => open('payment')}><Plus size={16} /> Receita</Button>
        <Button variant="secondary" onClick={() => open('expense')}><Plus size={16} /> Despesa</Button>
      </PageHeader>
      {message && <p className="mb-4 inline-flex rounded-full border border-green-400/20 bg-green-500/10 px-4 py-2 text-sm text-green-200"><CheckCircle2 size={16} /> {message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <section className="mb-4 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <Stat label="Receita do mes" value={formatCurrency(summary.revenue)} />
        <Stat label="Despesas do mes" value={formatCurrency(summary.costs)} />
        <Stat label="Lucro estimado" value={formatCurrency(summary.profit)} />
        <Stat label="Pendentes" value={summary.pending} />
        <Stat label="Atrasados" value={summary.late} />
        <Stat label="Total recebido" value={formatCurrency(summary.received)} />
      </section>
      <div className="mb-4 flex flex-wrap gap-2">
        {tabs.map((tab) => <Button key={tab} variant={active === tab ? 'primary' : 'secondary'} onClick={() => setActive(tab)}>{tab}</Button>)}
      </div>
      {(active === 'Resumo' || active === 'Receitas' || active === 'Pendentes' || active === 'Atrasados') && <FinanceTable title="Receitas" rows={visiblePayments} empty="Voce ainda nao possui receitas cadastradas." onPaid={(id) => markPaid('payment', id)} />}
      {(active === 'Resumo' || active === 'Despesas' || active === 'Pendentes' || active === 'Atrasados') && <FinanceTable title="Despesas" rows={visibleExpenses} empty="Voce ainda nao possui despesas cadastradas." onPaid={(id) => markPaid('expense', id)} expense />}
      <Modal title={modal === 'payment' ? 'Nova receita' : 'Nova despesa'} open={Boolean(modal)} onClose={() => setModal(null)}>
        <form onSubmit={save} className="grid gap-3">
          {modal === 'payment' && <EntityAutocomplete label="Cliente (opcional)" types={['clients']} value={client} onSelect={setClient} placeholder="Buscar cliente..." />}
          <Input label="Descricao" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} required />
          <Input label="Valor" type="number" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} required />
          <Input label="Vencimento" type="date" value={form.due_date} onChange={(e) => setForm({ ...form, due_date: e.target.value })} />
          <Select label="Status" value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>{financialStatusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select>
          <Select label="Forma de pagamento" value={form.payment_method} onChange={(e) => setForm({ ...form, payment_method: e.target.value })}><option value="">Selecione</option>{paymentMethodOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select>
          <Button>Salvar</Button>
        </form>
      </Modal>
    </>
  );
}

function Stat({ label, value }) {
  return <Card><p className="text-xs text-slate-400">{label}</p><strong className="text-xl">{value}</strong></Card>;
}

function FinanceTable({ title, rows, empty, onPaid, expense = false }) {
  return (
    <Card className="mb-4">
      <h2 className="mb-3 text-base font-bold">{title}</h2>
      {rows.length ? <Table rows={rows} columns={[
        { key: 'description', label: 'Descricao' },
        { key: expense ? 'category' : 'client', label: expense ? 'Categoria' : 'Cliente', render: (row) => expense ? (row.category || '-') : (row.client?.name || '-') },
        { key: 'amount', label: 'Valor', render: (row) => formatCurrency(row.amount) },
        { key: 'due_date', label: 'Vencimento', render: (row) => formatDate(row.due_date) },
        { key: 'status', label: 'Status', render: (row) => <Badge>{row.status}</Badge> },
        { key: 'payment_method', label: 'Forma' },
      ]} renderActions={(row) => <div className="flex gap-2">{row.status !== 'pago' && <Button variant="secondary" onClick={() => onPaid(row.id)}>Pago</Button>}</div>} /> : <EmptyState title={empty} />}
    </Card>
  );
}
