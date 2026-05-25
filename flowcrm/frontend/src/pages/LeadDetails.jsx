import { useParams } from 'react-router-dom';
import Card from '../components/ui/Card';
import PageHeader from '../components/shared/PageHeader';
import Timeline from '../components/shared/Timeline';

export default function LeadDetails() {
  const { id } = useParams();
  return <><PageHeader title={`Lead #${id}`} subtitle="Dados comerciais, timeline, proposta e próximas ações." /><div className="grid gap-4 xl:grid-cols-[1.2fr_.8fr]"><Card><h2 className="text-xl font-bold">Perfil do lead</h2><p className="mt-2 text-slate-400">Conversão e perda estão disponíveis na API.</p></Card><Card><Timeline /></Card></div></>;
}
