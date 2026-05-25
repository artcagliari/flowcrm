import appointmentsApi from '../api/appointments';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Appointments() {
  return <ResourcePage title="Agenda" subtitle="Compromissos, reuniões, retornos e atendimentos." api={appointmentsApi} modalTitle="compromisso" defaults={{ status: 'agendado', type: 'reuniao' }} fields={[{ name: 'title', label: 'Título' }, { name: 'type', label: 'Tipo' }, { name: 'starts_at', label: 'Início', type: 'datetime-local' }, { name: 'ends_at', label: 'Fim', type: 'datetime-local' }, { name: 'status', label: 'Status' }]} columns={[{ key: 'title', label: 'Compromisso' }, { key: 'type', label: 'Tipo' }, { key: 'starts_at', label: 'Início' }, statusColumn()]} />;
}
