import expensesApi from '../api/expenses';
import paymentsApi from '../api/payments';
import Card from '../components/ui/Card';
import PageHeader from '../components/shared/PageHeader';
import { financialStatusOptions, paymentMethodOptions } from '../utils/constants';
import { formatCurrency } from '../utils/formatCurrency';
import { formatDate } from '../utils/formatDate';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Finance() {
  return (
    <>
      <PageHeader title="Financeiro" subtitle="Acompanhe receitas, despesas, valores pendentes e atrasados." />
      <section className="mb-4 grid gap-4 md:grid-cols-3">
        <Card><p className="text-sm text-slate-400">Receita do mes</p><strong className="text-2xl">{formatCurrency(0)}</strong></Card>
        <Card><p className="text-sm text-slate-400">Despesa do mes</p><strong className="text-2xl">{formatCurrency(0)}</strong></Card>
        <Card><p className="text-sm text-slate-400">Lucro estimado</p><strong className="text-2xl">{formatCurrency(0)}</strong></Card>
      </section>
      <div className="grid gap-4 xl:grid-cols-2">
        <ResourcePage
          title="Pagamentos"
          subtitle="Receitas por cliente."
          api={paymentsApi}
          modalTitle="pagamento"
          defaults={{ status: 'pendente' }}
          fields={[
            { name: 'description', label: 'Descricao' },
            { name: 'amount', label: 'Valor', type: 'number' },
            { name: 'due_date', label: 'Vencimento', type: 'date' },
            { name: 'status', label: 'Status', options: financialStatusOptions },
          ]}
          columns={[
            { key: 'description', label: 'Descricao' },
            { key: 'amount', label: 'Valor', render: (row) => formatCurrency(row.amount) },
            { key: 'due_date', label: 'Vencimento', render: (row) => formatDate(row.due_date) },
            statusColumn(),
          ]}
          filters={[
            { name: 'status', label: 'Status', options: financialStatusOptions },
            { name: 'payment_method', label: 'Forma', options: paymentMethodOptions },
          ]}
          sortOptions={[
            { value: 'created_at', label: 'Data de criação' },
            { value: 'due_date', label: 'Vencimento' },
            { value: 'amount', label: 'Valor' },
            { value: 'status', label: 'Status' },
          ]}
        />
        <ResourcePage
          title="Despesas"
          subtitle="Saidas operacionais."
          api={expensesApi}
          modalTitle="despesa"
          defaults={{ status: 'pendente' }}
          fields={[
            { name: 'description', label: 'Descricao' },
            { name: 'amount', label: 'Valor', type: 'number' },
            { name: 'due_date', label: 'Vencimento', type: 'date' },
            { name: 'status', label: 'Status', options: financialStatusOptions },
          ]}
          columns={[
            { key: 'description', label: 'Descricao' },
            { key: 'amount', label: 'Valor', render: (row) => formatCurrency(row.amount) },
            { key: 'due_date', label: 'Vencimento', render: (row) => formatDate(row.due_date) },
            statusColumn(),
          ]}
          filters={[
            { name: 'status', label: 'Status', options: financialStatusOptions },
            { name: 'payment_method', label: 'Forma', options: paymentMethodOptions },
          ]}
          sortOptions={[
            { value: 'created_at', label: 'Data de criação' },
            { value: 'due_date', label: 'Vencimento' },
            { value: 'amount', label: 'Valor' },
            { value: 'status', label: 'Status' },
          ]}
        />
      </div>
    </>
  );
}
