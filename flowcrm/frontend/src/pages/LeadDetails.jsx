import { ArrowRight, Ban, CheckCircle2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { convertLead, getLeadDetails, markLeadLost } from '../api/leads';
import PageHeader from '../components/shared/PageHeader';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import { formatCurrency } from '../utils/formatCurrency';
import { formatDate, formatDateTime } from '../utils/formatDate';
import { handleApiError } from '../utils/handleApiError';

const tabs = ['Visao geral', 'Historico', 'Tarefas', 'Agenda', 'Documentos', 'Notas'];

export default function LeadDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [active, setActive] = useState('Visao geral');
  const [lostOpen, setLostOpen] = useState(false);
  const [lostReason, setLostReason] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function load() {
    setData(await getLeadDetails(id));
  }

  useEffect(() => { load(); }, [id]);

  async function convert() {
    setError('');
    try {
      const result = await convertLead(id);
      navigate(`/clients/${result.client.id}`);
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel converter o lead.').message);
    }
  }

  async function lose(event) {
    event.preventDefault();
    setError('');
    try {
      await markLeadLost(id, lostReason);
      setLostOpen(false);
      setMessage('Lead marcado como perdido.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel marcar como perdido.').message);
    }
  }

  if (!data) return null;
  const lead = data.lead;

  return (
    <>
      <PageHeader title={lead.name} subtitle="Acompanhe qualificacao, tarefas, agenda e materiais antes da conversao.">
        <Button variant="secondary" onClick={() => setLostOpen(true)}><Ban size={16} /> Perdido</Button>
        <Button onClick={convert}><ArrowRight size={16} /> Converter em cliente</Button>
      </PageHeader>
      {message && <p className="mb-4 inline-flex items-center gap-2 rounded-full border border-green-400/20 bg-green-500/10 px-4 py-2 text-sm text-green-200"><CheckCircle2 size={16} /> {message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <div className="mb-4 flex flex-wrap gap-2">{tabs.map((tab) => <Button key={tab} variant={active === tab ? 'primary' : 'secondary'} onClick={() => setActive(tab)}>{tab}</Button>)}</div>
      {active === 'Visao geral' && <Overview lead={lead} />}
      {active === 'Historico' && <SimpleList items={data.activities} empty="Nenhuma atividade registrada." render={(item) => <><Badge>{item.action}</Badge><strong className="ml-2">{item.description}</strong><p className="text-sm text-slate-400">{formatDateTime(item.created_at)} - {item.user?.name || 'Sistema'}</p></>} />}
      {active === 'Tarefas' && <SimpleList items={data.tasks} empty="Nenhuma tarefa vinculada." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{item.priority} - {item.status} - {formatDateTime(item.due_at)}</p></>} />}
      {active === 'Agenda' && <SimpleList items={data.appointments} empty="Nenhum compromisso vinculado." render={(item) => <><strong>{item.title}</strong><p className="text-sm text-slate-400">{item.type} - {item.status} - {formatDateTime(item.start_at || item.starts_at)}</p></>} />}
      {active === 'Documentos' && <SimpleList items={data.documents} empty="Nenhum documento vinculado." render={(item) => <><strong>{item.name}</strong><p className="text-sm text-slate-400">{item.category} - {formatDate(item.created_at)}</p></>} />}
      {active === 'Notas' && <SimpleList items={data.notes} empty="Nenhuma nota vinculada." render={(item) => <><strong>{item.type || 'Nota'}</strong><p className="text-sm text-slate-400">{item.content || item.body}</p></>} />}
      <Modal title="Marcar lead como perdido" open={lostOpen} onClose={() => setLostOpen(false)}>
        <form className="grid gap-3" onSubmit={lose}>
          <Input label="Motivo" value={lostReason} onChange={(event) => setLostReason(event.target.value)} />
          <Button>Salvar</Button>
        </form>
      </Modal>
    </>
  );
}

function Overview({ lead }) {
  const fields = [
    ['Telefone', lead.phone],
    ['WhatsApp', lead.whatsapp],
    ['E-mail', lead.email],
    ['Origem', lead.origin],
    ['Interesse', lead.interest],
    ['Temperatura', lead.temperature],
    ['Status', lead.status],
    ['Valor estimado', formatCurrency(lead.estimated_value)],
    ['Proxima acao', formatDateTime(lead.next_action_at)],
    ['Cadastro', formatDate(lead.created_at)],
  ];

  return (
    <Card>
      <div className="mb-4 flex flex-wrap gap-2">
        <Badge>{lead.status}</Badge>
        <Badge>{lead.temperature}</Badge>
        {lead.stage?.name && <Badge>{lead.stage.name}</Badge>}
      </div>
      <div className="grid gap-3 md:grid-cols-3">{fields.map(([label, value]) => <div key={label} className="rounded-2xl border border-white/10 bg-white/5 p-3"><span className="block text-xs text-slate-500">{label}</span><strong>{value || '-'}</strong></div>)}</div>
      {lead.whatsapp && <a className="mt-4 inline-flex text-sm text-sky-300" href={`https://wa.me/${lead.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noreferrer">Abrir WhatsApp</a>}
      {lead.notes && <p className="mt-4 text-sm text-slate-300">{lead.notes}</p>}
      <Link className="mt-4 inline-flex text-sm text-sky-300" to="/leads">Voltar para leads</Link>
    </Card>
  );
}

function SimpleList({ items = [], empty, render }) {
  return <Card>{items.length ? <div className="grid gap-3">{items.map((item) => <div key={item.id} className="rounded-2xl border border-white/10 bg-white/5 p-3">{render(item)}</div>)}</div> : <EmptyState title={empty} />}</Card>;
}
