import tasksApi from '../api/tasks';
import { taskPriorityOptions, taskStatusOptions } from '../utils/constants';
import { formatDateTime } from '../utils/formatDate';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Tasks() {
  return (
    <ResourcePage
      title="Tarefas"
      subtitle="Gerencie pendencias, prazos e atividades da sua rotina."
      api={tasksApi}
      modalTitle="tarefa"
      defaults={{ status: 'pendente', priority: 'media' }}
      fields={[
        { name: 'title', label: 'Titulo' },
        { name: 'description', label: 'Descricao' },
        { name: 'due_at', label: 'Prazo', type: 'datetime-local' },
        { name: 'priority', label: 'Prioridade', options: taskPriorityOptions },
        { name: 'status', label: 'Status', options: taskStatusOptions },
      ]}
      columns={[
        { key: 'title', label: 'Tarefa' },
        { key: 'priority', label: 'Prioridade' },
        { key: 'due_at', label: 'Prazo', render: (row) => formatDateTime(row.due_at || row.due_date) },
        statusColumn(),
      ]}
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
