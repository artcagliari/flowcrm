import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import PageHeader from '../components/shared/PageHeader';

export default function Settings() {
  return <><PageHeader title="Configurações" subtitle="Empresa, preferências, notificações e aparência."><Button>Salvar</Button></PageHeader><Card><div className="grid gap-3 md:grid-cols-2"><Input label="Nome da empresa" /><Input label="CNPJ" /><Input label="E-mail fiscal" /><Input label="Cor principal" defaultValue="#4F8CFF" /></div></Card></>;
}
