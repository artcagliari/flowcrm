import tasksApi from '../api/tasks';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Tasks() {
  return <ResourcePage title="Tarefas" subtitle="Gerencie retornos, follow-ups e prazos." api={tasksApi} modalTitle="tarefa" defaults={{ status: 'pendente', priority: 'media' }} fields={[{ name: 'title', label: 'Título' }, { name: 'description', label: 'Descrição' }, { name: 'due_at', label: 'Prazo', type: 'datetime-local' }, { name: 'priority', label: 'Prioridade' }, { name: 'status', label: 'Status' }]} columns={[{ key: 'title', label: 'Tarefa' }, { key: 'priority', label: 'Prioridade' }, { key: 'due_at', label: 'Prazo' }, statusColumn()]} />;
}
