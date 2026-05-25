import paymentsApi from '../api/payments';
import expensesApi from '../api/expenses';
import ResourcePage, { statusColumn } from './ResourcePage';
import Card from '../components/ui/Card';
import PageHeader from '../components/shared/PageHeader';

export default function Finance() {
  return <><PageHeader title="Financeiro" subtitle="Receitas, despesas, status e previsões." /><section className="mb-4 grid gap-4 md:grid-cols-3"><Card><p className="text-slate-400">Receita total</p><strong className="text-3xl">R$ 0,00</strong></Card><Card><p className="text-slate-400">Despesas</p><strong className="text-3xl">R$ 0,00</strong></Card><Card><p className="text-slate-400">Lucro estimado</p><strong className="text-3xl">R$ 0,00</strong></Card></section><div className="grid gap-4 xl:grid-cols-2"><ResourcePage title="Pagamentos" subtitle="Receitas por cliente." api={paymentsApi} modalTitle="pagamento" fields={[{ name: 'description', label: 'Descrição' }, { name: 'amount', label: 'Valor', type: 'number' }, { name: 'due_date', label: 'Vencimento', type: 'date' }, { name: 'status', label: 'Status' }]} columns={[{ key: 'description', label: 'Descrição' }, { key: 'amount', label: 'Valor' }, statusColumn()]} /><ResourcePage title="Despesas" subtitle="Saídas operacionais." api={expensesApi} modalTitle="despesa" fields={[{ name: 'description', label: 'Descrição' }, { name: 'amount', label: 'Valor', type: 'number' }, { name: 'due_date', label: 'Vencimento', type: 'date' }, { name: 'status', label: 'Status' }]} columns={[{ key: 'description', label: 'Descrição' }, { key: 'amount', label: 'Valor' }, statusColumn()]} /></div></>;
}
