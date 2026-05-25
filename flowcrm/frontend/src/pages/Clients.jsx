import clientsApi from '../api/clients';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Clients() {
  return <ResourcePage title="Clientes" subtitle="Organize clientes, contatos, responsáveis e histórico." api={clientsApi} modalTitle="cliente" defaults={{ status: 'ativo' }} fields={[{ name: 'name', label: 'Nome' }, { name: 'email', label: 'E-mail' }, { name: 'phone', label: 'Telefone' }, { name: 'city', label: 'Cidade' }, { name: 'origin', label: 'Origem' }, { name: 'status', label: 'Status' }]} columns={[{ key: 'name', label: 'Cliente' }, { key: 'email', label: 'E-mail' }, { key: 'phone', label: 'Telefone' }, { key: 'city', label: 'Cidade' }, statusColumn()]} />;
}
