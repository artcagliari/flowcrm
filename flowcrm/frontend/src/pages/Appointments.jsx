import appointmentsApi from '../api/appointments';
import { appointmentStatusOptions, appointmentTypeOptions } from '../utils/constants';
import { formatDateTime, fromApiDateTime, toApiDateTime } from '../utils/formatDate';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Appointments() {
  return (
    <ResourcePage
      title="Agenda"
      subtitle="Controle reunioes, atendimentos, retornos e compromissos importantes."
      api={appointmentsApi}
      modalTitle="compromisso"
      defaults={{ status: 'agendado', type: 'reuniao', date: '', start_time: '', end_time: '' }}
      transformSubmit={(form) => ({
        ...form,
        starts_at: toApiDateTime(form.date, form.start_time),
        start_at: toApiDateTime(form.date, form.start_time),
        ends_at: form.end_time ? toApiDateTime(form.date, form.end_time) : null,
        end_at: form.end_time ? toApiDateTime(form.date, form.end_time) : null,
      })}
      prepareEdit={(row) => {
        const start = fromApiDateTime(row.start_at || row.starts_at);
        const end = fromApiDateTime(row.end_at || row.ends_at);
        return { ...row, date: start.date, start_time: start.time, end_time: end.time };
      }}
      fields={[
        { name: 'title', label: 'Titulo' },
        { name: 'date', label: 'Data', type: 'date' },
        { name: 'start_time', label: 'Horario inicial', type: 'time' },
        { name: 'end_time', label: 'Horario final', type: 'time' },
        { name: 'type', label: 'Tipo', options: appointmentTypeOptions },
        { name: 'status', label: 'Status', options: appointmentStatusOptions },
        { name: 'description', label: 'Descricao opcional' },
      ]}
      columns={[
        { key: 'title', label: 'Compromisso' },
        { key: 'type', label: 'Tipo' },
        { key: 'starts_at', label: 'Inicio', render: (row) => formatDateTime(row.start_at || row.starts_at) },
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
