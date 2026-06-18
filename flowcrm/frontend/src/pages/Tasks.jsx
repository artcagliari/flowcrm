import tasksApi, { completeTask } from '../api/tasks';
import EntityAutocomplete from '../components/shared/EntityAutocomplete';
import Button from '../components/ui/Button';
import { taskPriorityOptions, taskStatusOptions } from '../utils/constants';
import { formatDateTime } from '../utils/formatDate';
import ResourcePage, { statusColumn } from './ResourcePage';

function entityFromRow(row) {
  if (row.client) return { type: 'clients', id: row.client.id, name: row.client.name };
  if (row.lead) return { type: 'leads', id: row.lead.id, name: row.lead.name };
  return null;
}

export default function Tasks() {
  return (
    <ResourcePage
      title="Tarefas"
      subtitle="Gerencie pendencias, prazos e atividades da sua rotina."
      api={tasksApi}
      modalTitle="tarefa"
      defaults={{ status: 'pendente', priority: 'media' }}
      transformSubmit={({ _entity, ...rest }) => ({
        ...rest,
        client_id: _entity?.type === 'clients' ? _entity.id : null,
        lead_id: _entity?.type === 'leads' ? _entity.id : null,
      })}
      prepareEdit={(row) => ({ ...row, _entity: entityFromRow(row) })}
      fields={[
        { name: 'title', label: 'Titulo' },
        { name: 'description', label: 'Descricao' },
        { name: 'due_at', label: 'Prazo', type: 'datetime-local' },
        { name: 'priority', label: 'Prioridade', options: taskPriorityOptions },
        { name: 'status', label: 'Status', options: taskStatusOptions },
        { name: '_entity', label: 'Cliente ou lead', render: ({ form, setForm }) => <EntityAutocomplete label="Cliente ou lead (opcional)" types={['clients', 'leads']} placeholder="Buscar cliente ou lead..." value={form._entity} onSelect={(item) => setForm({ ...form, _entity: item })} /> },
      ]}
      columns={[
        { key: 'title', label: 'Tarefa' },
        { key: 'priority', label: 'Prioridade' },
        { key: 'client', label: 'Cliente / Lead', render: (row) => row.client?.name || row.lead?.name || '-' },
        { key: 'due_at', label: 'Prazo', render: (row) => formatDateTime(row.due_at || row.due_date) },
        statusColumn(),
      ]}
      extraActions={(row, reload) => row.status !== 'concluida' && <Button variant="secondary" onClick={async () => { await completeTask(row.id); reload(); }}>Concluir</Button>}
      filters={[
        { name: 'status', label: 'Status', options: taskStatusOptions },
        { name: 'priority', label: 'Prioridade', options: taskPriorityOptions },
      ]}
      sortOptions={[
        { value: 'created_at', label: 'Data de criação' },
        { value: 'due_at', label: 'Prazo' },
        { value: 'priority', label: 'Prioridade' },
        { value: 'status', label: 'Status' },
        { value: 'title', label: 'Título' },
      ]}
    />
  );
}
