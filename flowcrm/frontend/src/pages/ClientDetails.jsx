import { FileUp, MessageSquare, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { createClientAppointment, createClientNote, createClientPayment, createClientTask, getClientDetails, uploadClientDocument } from '../api/clients';
import PageHeader from '../components/shared/PageHeader';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import Select from '../components/ui/Select';
import Textarea from '../components/ui/Textarea';
import { appointmentStatusOptions, appointmentTypeOptions, financialStatusOptions, taskPriorityOptions, taskStatusOptions } from '../utils/constants';
import { formatCurrency } from '../utils/formatCurrency';
import { formatDate, formatDateTime, toApiDateTime } from '../utils/formatDate';
import { handleApiError } from '../utils/handleApiError';

const tabs = ['Visao geral', 'Historico', 'Tarefas', 'Agenda', 'Financeiro', 'Documentos', 'Notas'];

export default function ClientDetails() {
  const { id } = useParams();
  const [data, setData] = useState(null);
  const [active, setActive] = useState('Visao geral');
  const [modal, setModal] = useState(null);
  const [form, setForm] = useState({});
  const [file, setFile] = useState(null);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function load() { setData(await getClientDetails(id)); }
  useEffect(() => { load(); }, [id]);
  if (!data) return null;
  const client = data.client;

  function open(type) {
    setModal(type);
    setError('');
    setForm(type === 'task' ? { status: 'pendente', priority: 'media' } : type === 'appointment' ? { status: 'agendado', type: 'reuniao' } : type === 'payment' ? { status: 'pendente' } : {});
  }

  async function submit(event) {
    event.preventDefault();
    setError('');
    try {
      if (modal === 'task') await createClientTask(id, form);
      if (modal === 'appointment') await createClientAppointment(id, { ...form, starts_at: toApiDateTime(form.date, form.start_time), start_at: toApiDateTime(form.date, form.start_time), ends_at: form.end_time ? toApiDateTime(form.date, form.end_time) : null });
      if (modal === 'payment') await createClientPayment(id, form);
      if (modal === 'note') await createClientNote(id, { ...form, content: form.body });
      if (modal === 'document') await uploadClientDocument(id, { ...form, file });
      setModal(null);
      setFile(null);
      setMessage('Operacao concluida.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel salvar.').message);
    }
  }

  return (
    <>
      <PageHeader title={client.name} subtitle="Centralize dados, historico, tarefas, agenda, financeiro, documentos e notas.">
        <Button variant="secondary" onClick={() => open('task')}><Plus size={16} /> Tarefa</Button>
        <Button variant="secondary" onClick={() => open('appointment')}><Plus size={16} /> Compromisso</Button>
        <Button variant="secondary" onClick={() => open('payment')}><Plus size={16} /> Pagamento</Button>
        <Button variant="secondary" onClick={() => open('document')}><FileUp size={16} /> Documento</Button>
        <Button onClick={() => open('note')}><MessageSquare size={16} /> Nota</Button>
      </PageHeader>
      {message && <p className="mb-4 inline-flex rounded-full border border-green-400/20 bg-green-500/10 px-4 py-2 text-sm text-green-200">{message}</p>}
      <div className="mb-4 flex flex-wrap gap-2">{tabs.map((tab) => <Button key={tab} variant={active === tab ? 'primary' : 'secondary'} onClick={() => setActive(tab)}>{tab}</Button>)}</div>
      {active === 'Visao geral' && <Overview client={client} />}
      {active === 'Historico' && <Timeline items={data.activities} />}
      {active === 'Tarefas' && <SimpleList items={data.tasks} empty="Nenhuma tarefa vinculada." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{item.priority} · {item.status} · {formatDateTime(item.due_at)}</p></>} />}
      {active === 'Agenda' && <SimpleList items={data.appointments} empty="Nenhum compromisso vinculado." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{item.type} · {item.status} · {formatDateTime(item.start_at || item.starts_at)}</p></>} />}
      {active === 'Financeiro' && <SimpleList items={data.payments} empty="Nenhum pagamento vinculado." render={(item) => <><strong>{item.description}</strong><p className="text-sm text-slate-400">{formatCurrency(item.amount)} · {item.status} · {formatDate(item.due_date)}</p></>} />}
      {active === 'Documentos' && <SimpleList items={data.documents} empty="Nenhum documento vinculado." render={(item) => <><strong>{item.name}</strong><p className="text-sm text-slate-400">{item.category} · {formatDate(item.created_at)}</p></>} />}
      {active === 'Notas' && <SimpleList items={data.notes} empty="Nenhuma nota vinculada." render={(item) => <><strong>{item.type || 'Nota'}</strong><p className="text-sm text-slate-400">{item.content || item.body}</p></>} />}
      <Modal title={`Adicionar ${modal || ''}`} open={Boolean(modal)} onClose={() => setModal(null)}>
        {error && <p className="mb-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
        <form onSubmit={submit} className="grid gap-3">
          {modal === 'task' && <><Input label="Titulo" value={form.title || ''} onChange={(e) => setForm({ ...form, title: e.target.value })} required /><Textarea label="Descricao" value={form.description || ''} onChange={(e) => setForm({ ...form, description: e.target.value })} /><Input label="Prazo" type="datetime-local" value={form.due_at || ''} onChange={(e) => setForm({ ...form, due_at: e.target.value })} /><Select label="Prioridade" value={form.priority || 'media'} onChange={(e) => setForm({ ...form, priority: e.target.value })}>{taskPriorityOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</Select><Select label="Status" value={form.status || 'pendente'} onChange={(e) => setForm({ ...form, status: e.target.value })}>{taskStatusOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</Select></>}
          {modal === 'appointment' && <><Input label="Titulo" value={form.title || ''} onChange={(e) => setForm({ ...form, title: e.target.value })} required /><Input label="Data" type="date" value={form.date || ''} onChange={(e) => setForm({ ...form, date: e.target.value })} required /><Input label="Inicio" type="time" value={form.start_time || ''} onChange={(e) => setForm({ ...form, start_time: e.target.value })} required /><Input label="Fim" type="time" value={form.end_time || ''} onChange={(e) => setForm({ ...form, end_time: e.target.value })} /><Select label="Tipo" value={form.type || 'reuniao'} onChange={(e) => setForm({ ...form, type: e.target.value })}>{appointmentTypeOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</Select><Select label="Status" value={form.status || 'agendado'} onChange={(e) => setForm({ ...form, status: e.target.value })}>{appointmentStatusOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</Select></>}
          {modal === 'payment' && <><Input label="Descricao" value={form.description || ''} onChange={(e) => setForm({ ...form, description: e.target.value })} required /><Input label="Valor" type="number" value={form.amount || ''} onChange={(e) => setForm({ ...form, amount: e.target.value })} required /><Input label="Vencimento" type="date" value={form.due_date || ''} onChange={(e) => setForm({ ...form, due_date: e.target.value })} /><Select label="Status" value={form.status || 'pendente'} onChange={(e) => setForm({ ...form, status: e.target.value })}>{financialStatusOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}</Select></>}
          {modal === 'document' && <><Input label="Arquivo" type="file" onChange={(e) => setFile(e.target.files?.[0])} required /><p className="text-xs text-slate-400">PDF, DOC, DOCX, XLS, XLSX, CSV, TXT, imagens, ZIP e RAR ate 10 MB.</p><Input label="Categoria" value={form.category || 'outros'} onChange={(e) => setForm({ ...form, category: e.target.value })} /></>}
          {modal === 'note' && <><Textarea label="Nota" value={form.body || ''} onChange={(e) => setForm({ ...form, body: e.target.value })} required /><Input label="Tipo" value={form.type || 'geral'} onChange={(e) => setForm({ ...form, type: e.target.value })} /></>}
          <Button>Salvar</Button>
        </form>
      </Modal>
    </>
  );
}

function Overview({ client }) {
  const fields = [['Telefone', client.phone], ['WhatsApp', client.whatsapp], ['E-mail', client.email], ['Documento', client.document], ['Profissao', client.profession], ['Endereco', client.address], ['Cidade', client.city], ['Status', client.status], ['Origem', client.origin], ['Ultima interacao', formatDateTime(client.last_contact_at)], ['Cadastro', formatDate(client.created_at)]];
  return <Card><div className="grid gap-3 md:grid-cols-3">{fields.map(([label, value]) => <div key={label} className="rounded-2xl border border-white/10 bg-white/5 p-3"><span className="block text-xs text-slate-500">{label}</span><strong>{value || '-'}</strong></div>)}</div>{client.whatsapp && <a className="mt-4 inline-flex text-sm text-sky-300" href={`https://wa.me/${client.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noreferrer">Abrir WhatsApp</a>}</Card>;
}

function Timeline({ items = [] }) {
  return <SimpleList items={items} empty="Nenhuma atividade registrada." render={(item) => <><Badge>{item.action}</Badge><strong className="ml-2">{item.description}</strong><p className="text-sm text-slate-400">{formatDateTime(item.created_at)} · {item.user?.name || 'Sistema'}</p></>} />;
}

function SimpleList({ items = [], empty, render }) {
  return <Card>{items.length ? <div className="grid gap-3">{items.map((item) => <div key={item.id} className="rounded-2xl border border-white/10 bg-white/5 p-3">{render(item)}</div>)}</div> : <EmptyState title={empty} />}</Card>;
}
