import { Link } from 'react-router-dom';
import clientsApi from '../api/clients';
import WhatsappActionButton from '../components/shared/WhatsappActionButton';
import ClientStatusSelect from '../components/shared/ClientStatusSelect';
import useProfessionMode from '../hooks/useProfessionMode';
import { clientStatusOptions, originOptions } from '../utils/constants';
import ResourcePage from './ResourcePage';

export default function Clients() {
  const { config } = useProfessionMode();

  return (
    <ResourcePage
      title={config.clientsLabel}
      subtitle="Base de clientes da empresa. Use Mensagem para falar pelo WhatsApp."
      api={clientsApi}
      modalTitle="cliente"
      defaults={{ status: 'encaminhado' }}
      fields={[
        { name: 'name', label: 'Nome' },
        { name: 'phone', label: 'Celular' },
        { name: 'whatsapp', label: 'WhatsApp' },
        { name: 'email', label: 'E-mail' },
        { name: 'city', label: 'Cidade' },
        { name: 'origin', label: 'Origem' },
        { name: 'status', label: 'Status', options: clientStatusOptions },
      ]}
      columns={[
        {
          key: 'name',
          label: config.clientsLabel.slice(0, -1),
          render: (row) => (
            <div className="flex items-center gap-2">
              <Link className="text-sky-300 hover:text-sky-200" to={`/clients/${row.id}`}>{row.name}</Link>
              <WhatsappActionButton clientId={row.id} phone={row.phone} whatsapp={row.whatsapp} compact />
            </div>
          ),
        },
        { key: 'phone', label: 'Celular', render: (row) => row.whatsapp || row.phone || '-' },
        { key: 'email', label: 'E-mail' },
        { key: 'city', label: 'Cidade' },
        {
          key: 'status',
          label: 'Status',
          render: (row) => <ClientStatusSelect clientId={row.id} value={row.status} />,
        },
      ]}
      extraActions={(row) => (
        <WhatsappActionButton clientId={row.id} phone={row.phone} whatsapp={row.whatsapp} />
      )}
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
