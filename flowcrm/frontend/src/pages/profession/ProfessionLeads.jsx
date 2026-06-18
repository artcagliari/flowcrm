import { Link } from 'react-router-dom';
import leadsApi, { discardContact, forwardContact } from '../../api/leads';
import WhatsappActionButton from '../../components/shared/WhatsappActionButton';
import { contactStatusOptions, originOptions } from '../../utils/constants';
import useProfessionMode from '../../hooks/useProfessionMode';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import ResourcePage from '../ResourcePage';

export default function ProfessionLeads() {
  const { config } = useProfessionMode();

  const columns = [
    {
      key: 'name',
      label: 'Lead',
      render: (row) => (
        <div className="flex items-center gap-2">
          <Link className="text-sky-300 hover:text-sky-200" to={`/leads/${row.id}`}>{row.name}</Link>
          <WhatsappActionButton leadId={row.id} phone={row.phone} whatsapp={row.whatsapp} compact />
        </div>
      ),
    },
    {
      key: 'phone',
      label: 'Celular',
      render: (row) => row.whatsapp || row.phone || '-',
    },
    { key: 'interest', label: 'Assunto' },
    { key: 'origin', label: 'Origem' },
    { key: 'status', label: 'Situacao', render: (row) => <Badge>{contactStatusOptions.find((o) => o.value === row.status)?.label || row.status || 'Novo'}</Badge> },
  ];

  return (
    <ResourcePage
      title={config.leadsLabel}
      subtitle={config.leadsSubtitle}
      api={leadsApi}
      modalTitle="lead"
      defaults={{ status: 'novo' }}
      fields={config.leadFields}
      columns={columns}
      extraActions={(row, reload) => row.status !== 'encaminhado' && row.status !== 'descartado' ? (
        <div className="flex flex-wrap items-center gap-2">
          <WhatsappActionButton leadId={row.id} phone={row.phone} whatsapp={row.whatsapp} />
          <Button
            variant="secondary"
            className="!px-2 !py-1 !text-xs"
            onClick={async () => { await forwardContact(row.id); reload(); }}
          >
            Converter em cliente
          </Button>
          <Button
            variant="ghost"
            className="!px-2 !py-1 !text-xs"
            onClick={async () => { if (window.confirm('Descartar este contato?')) { await discardContact(row.id); reload(); } }}
          >
            Descartar
          </Button>
        </div>
      ) : (
        <WhatsappActionButton leadId={row.id} phone={row.phone} whatsapp={row.whatsapp} />
      )}
      filters={[
        { name: 'status', label: 'Situacao', options: contactStatusOptions },
        { name: 'origin', label: 'Origem', options: originOptions },
      ]}
      sortOptions={[
        { value: 'created_at', label: 'Data de cadastro' },
        { value: 'name', label: 'Nome' },
      ]}
    />
  );
}
