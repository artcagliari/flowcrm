import { MessageCircle, Plus, Send, Settings } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  listConversations,
  listMessages,
  markConversationRead,
  sendWhatsappMessage,
  startConversation,
} from '../api/whatsapp';
import { listMessageTemplates } from '../api/templates';
import EntityAutocomplete from '../components/shared/EntityAutocomplete';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import { handleApiError } from '../utils/handleApiError';
import { formatDateTime } from '../utils/formatDate';

export default function WhatsappInbox() {
  const [conversations, setConversations] = useState([]);
  const [providerOnline, setProviderOnline] = useState(false);
  const [activeId, setActiveId] = useState(null);
  const [messages, setMessages] = useState([]);
  const [body, setBody] = useState('');
  const [loading, setLoading] = useState(true);
  const [templates, setTemplates] = useState([]);
  const [newOpen, setNewOpen] = useState(false);
  const [entity, setEntity] = useState(null);
  const [phone, setPhone] = useState('');
  const [error, setError] = useState('');

  const loadConversations = () => listConversations().then((data) => {
    const list = data?.conversations?.data || data?.conversations || [];
    setConversations(list);
    setProviderOnline(Boolean(data?.provider_online));
  });

  useEffect(() => {
    loadConversations().finally(() => setLoading(false));
    listMessageTemplates('whatsapp').then(setTemplates).catch(() => setTemplates([]));
  }, []);

  useEffect(() => {
    if (!activeId) return;
    listMessages(activeId).then((data) => {
      setMessages(data?.data || data || []);
      markConversationRead(activeId).catch(() => {});
      loadConversations();
    });
  }, [activeId]);

  async function send() {
    if (!body.trim() || !activeId) return;
    await sendWhatsappMessage(activeId, body.trim());
    setBody('');
    const data = await listMessages(activeId);
    setMessages(data?.data || data || []);
    loadConversations();
  }

  async function createConversation(event) {
    event.preventDefault();
    setError('');
    try {
      const payload = entity
        ? { [entity.type === 'clients' ? 'client_id' : 'lead_id']: entity.id }
        : { phone };
      const conversation = await startConversation(payload);
      setNewOpen(false);
      setEntity(null);
      setPhone('');
      await loadConversations();
      setActiveId(conversation.id);
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel iniciar a conversa.').message);
    }
  }

  const active = conversations.find((c) => c.id === activeId);

  return (
    <>
      <PageHeader
        title="WhatsApp Inbox"
        subtitle="Conversas integradas ao CRM. Cada contato vira lead automaticamente."
        action={(
          <div className="flex items-center gap-2">
            <span className={`rounded-full px-3 py-1 text-xs ${providerOnline ? 'bg-green-500/20 text-green-200' : 'bg-amber-500/20 text-amber-200'}`}>
              {providerOnline ? 'Provider conectado' : 'Modo log (dev)'}
            </span>
            <Button onClick={() => setNewOpen(true)}><Plus size={16} /> Nova conversa</Button>
            <Link to="/integrations"><Button variant="ghost"><Settings size={16} /></Button></Link>
          </div>
        )}
      />

      <div className="grid min-h-[560px] gap-4 lg:grid-cols-[320px_1fr]">
        <Card className="overflow-hidden p-0">
          <div className="border-b border-white/10 p-3">
            <strong className="text-sm">Conversas</strong>
          </div>
          <div className="max-h-[500px] overflow-auto">
            {loading && <p className="p-4 text-sm text-slate-400">Carregando...</p>}
            {!loading && conversations.length === 0 && (
              <div className="p-4 text-sm text-slate-400">
                <p className="mb-2">Nenhuma conversa ainda.</p>
                <p>Inicie pelo botao <strong>Nova conversa</strong>, pelo botao WhatsApp em um lead/cliente, ou conecte um provider em <Link to="/integrations" className="text-sky-300">Integracoes</Link>.</p>
              </div>
            )}
            {conversations.map((conv) => (
              <button
                key={conv.id}
                type="button"
                onClick={() => setActiveId(conv.id)}
                className={`w-full border-b border-white/5 p-3 text-left transition hover:bg-white/5 ${activeId === conv.id ? 'bg-white/10' : ''}`}
              >
                <div className="flex items-center justify-between gap-2">
                  <strong className="truncate text-sm">{conv.contact_name || conv.phone}</strong>
                  {conv.unread_count > 0 && (
                    <span className="rounded-full bg-sky-500 px-2 py-0.5 text-xs">{conv.unread_count}</span>
                  )}
                </div>
                <p className="truncate text-xs text-slate-400">{conv.latest_message?.body || conv.phone}</p>
                {(conv.client || conv.lead) && (
                  <p className="mt-1 flex gap-2 text-xs text-sky-300">
                    {conv.client && <Link to={`/clients/${conv.client.id}`} onClick={(e) => e.stopPropagation()}>Cliente</Link>}
                    {conv.lead && <Link to={`/leads/${conv.lead.id}`} onClick={(e) => e.stopPropagation()}>Lead</Link>}
                  </p>
                )}
              </button>
            ))}
          </div>
        </Card>

        <Card className="flex flex-col overflow-hidden p-0">
          {!active ? (
            <div className="grid flex-1 place-items-center p-8 text-center text-slate-400">
              <MessageCircle size={40} className="mb-3 opacity-40" />
              <p>Selecione uma conversa para ver mensagens</p>
            </div>
          ) : (
            <>
              <div className="flex items-center justify-between gap-2 border-b border-white/10 p-4">
                <div>
                  <strong>{active.contact_name || active.phone}</strong>
                  <p className="text-xs text-slate-400">{active.phone}</p>
                </div>
                {(active.client || active.lead) && (
                  <Link
                    to={active.client ? `/clients/${active.client.id}` : `/leads/${active.lead.id}`}
                    className="text-xs text-sky-300"
                  >
                    Abrir ficha
                  </Link>
                )}
              </div>
              <div className="flex-1 space-y-2 overflow-auto p-4">
                {messages.map((msg) => (
                  <div
                    key={msg.id}
                    className={`max-w-[80%] rounded-2xl px-3 py-2 text-sm ${msg.direction === 'out' ? 'ml-auto bg-sky-500/20 text-sky-100' : 'bg-white/10 text-slate-200'}`}
                  >
                    <p className="whitespace-pre-wrap">{msg.body}</p>
                    <span className="mt-1 block text-[10px] opacity-60">
                      {msg.status === 'failed' ? 'Falha no envio · ' : ''}{formatDateTime(msg.created_at)}
                    </span>
                  </div>
                ))}
                {messages.length === 0 && <p className="text-sm text-slate-500">Sem mensagens nesta conversa.</p>}
              </div>
              <div className="border-t border-white/10 p-3">
                {templates.length > 0 && (
                  <select
                    className="mb-2 w-full rounded-xl border border-white/10 bg-white/5 p-2 text-sm text-slate-200"
                    value=""
                    onChange={(e) => { if (e.target.value) setBody((prev) => (prev ? `${prev}\n${e.target.value}` : e.target.value)); }}
                  >
                    <option value="">Inserir template...</option>
                    {templates.map((tpl) => (
                      <option key={tpl.id} value={tpl.body}>{tpl.name}</option>
                    ))}
                  </select>
                )}
                <div className="flex gap-2">
                  <input
                    className="flex-1 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 outline-none focus:border-sky-400/50"
                    placeholder="Digite sua mensagem..."
                    value={body}
                    onChange={(e) => setBody(e.target.value)}
                    onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } }}
                  />
                  <Button onClick={send}><Send size={16} /></Button>
                </div>
              </div>
            </>
          )}
        </Card>
      </div>

      <Modal title="Nova conversa" open={newOpen} onClose={() => setNewOpen(false)}>
        {error && <p className="mb-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
        <form className="grid gap-4" onSubmit={createConversation}>
          <EntityAutocomplete label="Buscar lead ou cliente" value={entity} onSelect={setEntity} />
          <div className="text-center text-xs text-slate-500">ou</div>
          <Input label="Numero de telefone" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="(11) 99999-9999" disabled={Boolean(entity)} />
          <Button type="submit" disabled={!entity && !phone.trim()}>Iniciar conversa</Button>
        </form>
      </Modal>
    </>
  );
}
