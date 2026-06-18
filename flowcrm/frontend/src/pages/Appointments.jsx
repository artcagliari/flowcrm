import appointmentsApi, { cancelAppointment, completeAppointment } from '../api/appointments';
import EntityAutocomplete from '../components/shared/EntityAutocomplete';
import Button from '../components/ui/Button';
import { appointmentStatusOptions, appointmentTypeOptions } from '../utils/constants';
import { formatDateTime, fromApiDateTime, toApiDateTime } from '../utils/formatDate';
import ResourcePage, { statusColumn } from './ResourcePage';

function entityFromRow(row) {
  if (row.client) return { type: 'clients', id: row.client.id, name: row.client.name };
  if (row.lead) return { type: 'leads', id: row.lead.id, name: row.lead.name };
  return null;
}

export default function Appointments() {
  return (
    <ResourcePage
      title="Agenda"
      subtitle="Reunioes, visitas e compromissos comerciais."
      api={appointmentsApi}
      modalTitle="compromisso"
      createLabel="Novo compromisso"
      defaults={{ status: 'agendado', type: 'reuniao', date: '', start_time: '', end_time: '' }}
      validateForm={(form) => {
        if (!form._entity) {
          window.alert('Selecione o cliente ou lead para agendar o compromisso.');
          return false;
        }
        return true;
      }}
      transformSubmit={({ _entity, ...form }) => ({
        ...form,
        starts_at: toApiDateTime(form.date, form.start_time),
        ends_at: form.end_time ? toApiDateTime(form.date, form.end_time) : null,
        client_id: _entity?.type === 'clients' ? _entity.id : null,
        lead_id: _entity?.type === 'leads' ? _entity.id : null,
      })}
      prepareEdit={(row) => {
        const start = fromApiDateTime(row.starts_at);
        const end = fromApiDateTime(row.ends_at);
        return { ...row, date: start.date, start_time: start.time, end_time: end.time, _entity: entityFromRow(row) };
      }}
      fields={[
        { name: '_entity', label: 'Cliente ou lead', render: ({ form, setForm }) => (
          <EntityAutocomplete
            label="Cliente ou lead"
            types={['clients', 'leads']}
            placeholder="Buscar pelo nome..."
            value={form._entity}
            onSelect={(item) => setForm({
              ...form,
              _entity: item,
              title: form.title || (item ? `Reuniao - ${item.name}` : ''),
            })}
          />
        ) },
        { name: 'title', label: 'Titulo' },
        { name: 'date', label: 'Data', type: 'date' },
        { name: 'start_time', label: 'Horario inicial', type: 'time' },
        { name: 'end_time', label: 'Horario final', type: 'time' },
        { name: 'type', label: 'Tipo', options: appointmentTypeOptions },
        { name: 'status', label: 'Status', options: appointmentStatusOptions },
        { name: 'description', label: 'Observacoes opcionais' },
      ]}
      columns={[
        { key: 'title', label: 'Compromisso' },
        { key: 'type', label: 'Tipo' },
        { key: 'client', label: 'Cliente / Lead', render: (row) => row.client?.name || row.lead?.name || '-' },
        { key: 'starts_at', label: 'Inicio', render: (row) => formatDateTime(row.starts_at) },
        statusColumn(),
      ]}
      extraActions={(row, reload) => (
        <>
          {row.status !== 'concluido' && <Button variant="secondary" onClick={async () => { await completeAppointment(row.id); reload(); }}>Concluir</Button>}
          {row.status !== 'cancelado' && <Button variant="secondary" onClick={async () => { await cancelAppointment(row.id); reload(); }}>Cancelar</Button>}
        </>
      )}
      filters={[
        { name: 'status', label: 'Status', options: appointmentStatusOptions },
        { name: 'type', label: 'Tipo', options: appointmentTypeOptions },
      ]}
      sortOptions={[
        { value: 'starts_at', label: 'Data do compromisso' },
        { value: 'created_at', label: 'Data de criação' },
        { value: 'status', label: 'Status' },
        { value: 'type', label: 'Tipo' },
        { value: 'title', label: 'Título' },
      ]}
    />
  );
}
