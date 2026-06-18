import { ArrowRight, Ban, MessageSquare, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { convertLead, createLeadAppointment, createLeadNote, createLeadTask, discardContact, getLeadDetails, uploadLeadDocument } from '../api/leads';
import AiInsightsCard from '../components/shared/AiInsightsCard';
import Timeline from '../components/shared/Timeline';
import WhatsappActionButton from '../components/shared/WhatsappActionButton';
import PageHeader from '../components/shared/PageHeader';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import Select from '../components/ui/Select';
import Textarea from '../components/ui/Textarea';
import useProfessionMode from '../hooks/useProfessionMode';
import { appointmentStatusOptions, contactStatusOptions, taskPriorityOptions, taskStatusOptions } from '../utils/constants';
import { formatDate, formatDateTime, toApiDateTime } from '../utils/formatDate';
import { handleApiError } from '../utils/handleApiError';

const tabs = ['Visao geral', 'Historico', 'Tarefas', 'Agenda', 'Documentos', 'Notas'];

export default function LeadDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { config } = useProfessionMode();
  const [data, setData] = useState(null);
  const [active, setActive] = useState('Visao geral');
  const [discardOpen, setDiscardOpen] = useState(false);
  const [discardReason, setDiscardReason] = useState('');
  const [modal, setModal] = useState(null);
  const [form, setForm] = useState({});
  const [file, setFile] = useState(null);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const targetLabel = 'cliente';

  async function load() { setData(await getLeadDetails(id)); }
  useEffect(() => { load(); }, [id]);

  function open(type) {
    setModal(type);
    setError('');
    setForm(type === 'task' ? { status: 'pendente', priority: 'media' } : type === 'appointment' ? { status: 'agendado', type: 'reuniao' } : {});
  }

  async function submit(event) {
    event.preventDefault();
    setError('');
    try {
      if (modal === 'task') await createLeadTask(id, form);
      if (modal === 'appointment') await createLeadAppointment(id, { ...form, starts_at: toApiDateTime(form.date, form.start_time), ends_at: form.end_time ? toApiDateTime(form.date, form.end_time) : null });
      if (modal === 'note') await createLeadNote(id, { content: form.body, type: form.type || 'geral' });
      if (modal === 'document') await uploadLeadDocument(id, { ...form, file });
      setModal(null);
      setFile(null);
      setMessage('Salvo.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel salvar.').message);
    }
  }

  async function forward() {
    setError('');
    try {
      const result = await convertLead(id);
      navigate(`/clients/${result.client.id}`);
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel encaminhar.').message);
    }
  }

  async function discard(event) {
    event.preventDefault();
    setError('');
    try {
      await discardContact(id, { lost_reason: discardReason });
      setDiscardOpen(false);
      setMessage('Contato descartado.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel descartar.').message);
    }
  }

  if (!data) return null;
  const lead = data.lead;
  const statusLabel = contactStatusOptions.find((o) => o.value === lead.status)?.label || lead.status;

  return (
    <>
      <PageHeader title={lead.name} subtitle="Lead em prospeccao. Converta em cliente quando fechar o negocio.">
        <Button variant="secondary" onClick={() => open('appointment')}><Plus size={16} /> Agendar reuniao</Button>
        <Button variant="secondary" onClick={() => open('note')}><MessageSquare size={16} /> Nota</Button>
        <WhatsappActionButton leadId={Number(id)} phone={lead.phone} whatsapp={lead.whatsapp} label="Mensagem WhatsApp" />
        {lead.status !== 'encaminhado' && lead.status !== 'descartado' && (
          <>
            <Button variant="secondary" onClick={() => setDiscardOpen(true)}><Ban size={16} /> Descartar</Button>
            <Button onClick={forward}><ArrowRight size={16} /> Encaminhar para {targetLabel}</Button>
          </>
        )}
      </PageHeader>
      {message && <p className="mb-4 rounded-2xl border border-green-400/20 bg-green-500/10 p-3 text-sm text-green-200">{message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <div className="mb-4 flex flex-wrap gap-2">{tabs.map((tab) => <Button key={tab} variant={active === tab ? 'primary' : 'secondary'} onClick={() => setActive(tab)}>{tab}</Button>)}</div>
      {active === 'Visao geral' && (
        <div className="grid gap-4 xl:grid-cols-[1fr_360px]">
          <Overview lead={lead} statusLabel={statusLabel} />
          <AiInsightsCard type="lead" entityId={Number(id)} />
        </div>
      )}
      {active === 'Historico' && <Card><Timeline type="leads" id={id} /></Card>}
      {active === 'Tarefas' && <SimpleList items={data.tasks} empty="Nenhuma tarefa." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{item.status} · {formatDateTime(item.due_at)}</p></>} />}
      {active === 'Agenda' && <SimpleList items={data.appointments} empty="Nenhum compromisso." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{formatDateTime(item.starts_at)}</p></>} />}
      {active === 'Documentos' && <SimpleList items={data.documents} empty="Nenhum documento." render={(item) => <><strong>{item.name}</strong></>} />}
      {active === 'Notas' && <SimpleList items={data.notes} empty="Nenhuma nota." render={(item) => <><strong>{item.type || 'Nota'}</strong><p className="text-sm text-slate-400">{item.content}</p></>} />}
      <Modal title={`Adicionar ${modal || ''}`} open={Boolean(modal)} onClose={() => setModal(null)}>
        {error && <p className="mb-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
        <form onSubmit={submit} className="grid gap-3">
          {modal === 'task' && <><Input label="Titulo" value={form.title || ''} onChange={(e) => setForm({ ...form, title: e.target.value })} required /><Input label="Prazo" type="datetime-local" value={form.due_at || ''} onChange={(e) => setForm({ ...form, due_at: e.target.value })} /></>}
          {modal === 'appointment' && <><Input label="Titulo" value={form.title || ''} onChange={(e) => setForm({ ...form, title: e.target.value })} required /><Input label="Data" type="date" value={form.date || ''} onChange={(e) => setForm({ ...form, date: e.target.value })} required /><Input label="Inicio" type="time" value={form.start_time || ''} onChange={(e) => setForm({ ...form, start_time: e.target.value })} required /><Select label="Status" value={form.status || 'agendado'} onChange={(e) => setForm({ ...form, status: e.target.value })} options={appointmentStatusOptions} /></>}
          {modal === 'document' && <><Input label="Arquivo" type="file" onChange={(e) => setFile(e.target.files?.[0])} required /></>}
          {modal === 'note' && <><Textarea label="Nota" value={form.body || ''} onChange={(e) => setForm({ ...form, body: e.target.value })} required /></>}
          <Button>Salvar</Button>
        </form>
      </Modal>
      <Modal title="Descartar contato" open={discardOpen} onClose={() => setDiscardOpen(false)}>
        <form className="grid gap-3" onSubmit={discard}>
          <Input label="Motivo (opcional)" value={discardReason} onChange={(e) => setDiscardReason(e.target.value)} />
          <Button>Confirmar</Button>
        </form>
      </Modal>
    </>
  );
}

function Overview({ lead, statusLabel }) {
  const fields = [['Celular', lead.whatsapp || lead.phone], ['E-mail', lead.email], ['Origem', lead.origin], ['Assunto', lead.interest], ['Cadastro', formatDate(lead.created_at)]];
  return (
    <Card>
      <div className="mb-4 flex flex-wrap items-center gap-3">
        <Badge>{statusLabel}</Badge>
        <WhatsappActionButton leadId={lead.id} phone={lead.phone} whatsapp={lead.whatsapp} />
      </div>
      <div className="grid gap-3 md:grid-cols-3">{fields.map(([label, value]) => <div key={label} className="rounded-2xl border border-white/10 bg-white/5 p-3"><span className="block text-xs text-slate-500">{label}</span><strong>{value || '-'}</strong></div>)}</div>
      <Link className="mt-4 inline-flex text-sm text-sky-300" to="/leads">Voltar para contatos</Link>
    </Card>
  );
}

function SimpleList({ items = [], empty, render }) {
  return <Card>{items.length ? <div className="grid gap-3">{items.map((item) => <div key={item.id} className="rounded-2xl border border-white/10 bg-white/5 p-3">{render(item)}</div>)}</div> : <EmptyState title={empty} />}</Card>;
}
