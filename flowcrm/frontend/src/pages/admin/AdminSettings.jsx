import PageHeader from '../../components/shared/PageHeader';
import Card from '../../components/ui/Card';

export default function AdminSettings() {
  return (
    <>
      <PageHeader title="Configuracoes" subtitle="Preferencias globais da plataforma SaaS." />
      <Card><p className="text-sm text-slate-400">Configuracoes globais serao centralizadas aqui.</p></Card>
    </>
  );
}
