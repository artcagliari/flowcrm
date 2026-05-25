import Card from '../components/ui/Card';
import Avatar from '../components/ui/Avatar';
import Badge from '../components/ui/Badge';
import PageHeader from '../components/shared/PageHeader';

export default function Users() {
  return <><PageHeader title="Usuários" subtitle="Equipe, cargos e permissões." /><Card><div className="grid gap-3">{['Marina Alves', 'Rafael Souza', 'Bianca Prado'].map((name) => <div className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-3" key={name}><div className="flex items-center gap-3"><Avatar name={name} /><strong>{name}</strong></div><Badge>ativo</Badge></div>)}</div></Card></>;
}
