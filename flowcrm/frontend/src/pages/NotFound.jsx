import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import PageHeader from '../components/shared/PageHeader';

export default function NotFound() {
  return <><PageHeader title="404" subtitle="Página não encontrada."><Button onClick={() => location.href = '/'}>Dashboard</Button></PageHeader><Card><p className="text-slate-400">Volte para uma área ativa do FlowCRM.</p></Card></>;
}
