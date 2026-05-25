import clientsApi from '../api/clients';
import { clientStatusOptions, originOptions } from '../utils/constants';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Clients() {
  return (
    <ResourcePage
      title="Clientes"
      subtitle="Organize clientes, contatos, responsaveis e historico."
      api={clientsApi}
      modalTitle="cliente"
      defaults={{ status: 'ativo' }}
      fields={[
        { name: 'name', label: 'Nome' },
        { name: 'email', label: 'E-mail' },
        { name: 'phone', label: 'Telefone' },
        { name: 'city', label: 'Cidade' },
        { name: 'origin', label: 'Origem' },
        { name: 'status', label: 'Status', options: clientStatusOptions },
      ]}
      columns={[
        { key: 'name', label: 'Cliente' },
        { key: 'email', label: 'E-mail' },
        { key: 'phone', label: 'Telefone' },
        { key: 'city', label: 'Cidade' },
        statusColumn(),
      ]}
      filters={[
        { name: 'status', label: 'Status', options: clientStatusOptions },
        { name: 'origin', label: 'Origem', options: originOptions },
      ]}
      sortOptions={[
        { value: 'created_at', label: 'Data de cadastro' },
        { value: 'name', label: 'Nome' },
        { value: 'status', label: 'Status' },
        { value: 'city', label: 'Cidade' },
      ]}
    />
  );
}
