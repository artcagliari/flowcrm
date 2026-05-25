import appointmentsApi from '../api/appointments';
import { appointmentStatusOptions, appointmentTypeOptions } from '../utils/constants';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Appointments() {
  return (
    <ResourcePage
      title="Agenda"
      subtitle="Compromissos, reunioes, retornos e atendimentos."
      api={appointmentsApi}
      modalTitle="compromisso"
      defaults={{ status: 'agendado', type: 'reuniao' }}
      fields={[
        { name: 'title', label: 'Titulo' },
        { name: 'type', label: 'Tipo', options: appointmentTypeOptions },
        { name: 'starts_at', label: 'Inicio', type: 'datetime-local' },
        { name: 'ends_at', label: 'Fim', type: 'datetime-local' },
        { name: 'status', label: 'Status', options: appointmentStatusOptions },
      ]}
      columns={[
        { key: 'title', label: 'Compromisso' },
        { key: 'type', label: 'Tipo' },
        { key: 'starts_at', label: 'Inicio' },
        statusColumn(),
      ]}
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
