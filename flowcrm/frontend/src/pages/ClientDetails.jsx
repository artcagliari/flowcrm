import { useParams } from 'react-router-dom';
import Card from '../components/ui/Card';
import PageHeader from '../components/shared/PageHeader';
import Timeline from '../components/shared/Timeline';

export default function ClientDetails() {
  const { id } = useParams();
  return <><PageHeader title={`Cliente #${id}`} subtitle="Dados principais, histórico, tarefas, documentos e pagamentos." /><div className="grid gap-4 xl:grid-cols-[1.2fr_.8fr]"><Card><h2 className="text-xl font-bold">Perfil do cliente</h2><p className="mt-2 text-slate-400">Detalhes integrados à API em evolução.</p></Card><Card><Timeline /></Card></div></>;
}
