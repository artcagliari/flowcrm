import Card from '../components/ui/Card';
import Badge from '../components/ui/Badge';
import PageHeader from '../components/shared/PageHeader';

export default function Notifications() {
  const rows = ['Tarefa vence em 30 minutos', 'Pagamento atrasado', 'Novo lead cadastrado'];
  return <><PageHeader title="Notificações" subtitle="Alertas internos da operação." /><Card><div className="grid gap-3">{rows.map((row) => <div className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-3" key={row}><strong>{row}</strong><Badge>novo</Badge></div>)}</div></Card></>;
}
