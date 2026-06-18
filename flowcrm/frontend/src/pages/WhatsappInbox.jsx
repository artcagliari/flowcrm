import { MessageCircle, Send } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { listConversations, listMessages, markConversationRead, sendWhatsappMessage } from '../api/whatsapp';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import { formatDateTime } from '../utils/formatDate';

export default function WhatsappInbox() {
  const [conversations, setConversations] = useState([]);
  const [providerOnline, setProviderOnline] = useState(false);
  const [activeId, setActiveId] = useState(null);
  const [messages, setMessages] = useState([]);
  const [body, setBody] = useState('');
  const [loading, setLoading] = useState(true);

  const loadConversations = () => listConversations()
    .then((data) => {
      const list = data?.conversations?.data || data?.conversations || [];
      setConversations(list);
      setProviderOnline(Boolean(data?.provider_online));
    });

  useEffect(() => {
    loadConversations().finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    if (!activeId) return;
    listMessages(activeId).then((data) => {
      const list = data?.data || data || [];
      setMessages(list);
      markConversationRead(activeId).catch(() => {});
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

  const active = conversations.find((c) => c.id === activeId);

  return (
    <>
      <PageHeader
        title="WhatsApp Inbox"
        subtitle="Conversas integradas ao CRM. Configure o provider em Integracoes."
        action={(
          <span className={`rounded-full px-3 py-1 text-xs ${providerOnline ? 'bg-green-500/20 text-green-200' : 'bg-amber-500/20 text-amber-200'}`}>
            {providerOnline ? 'Provider conectado' : 'Modo log (dev)'}
          </span>
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
              <p className="p-4 text-sm text-slate-400">
                Nenhuma conversa ainda. Inicie pelo botao WhatsApp em um lead ou cliente.
              </p>
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
                <p className="truncate text-xs text-slate-400">
                  {conv.latest_message?.body || conv.phone}
                </p>
                {(conv.client || conv.lead) && (
                  <p className="mt-1 text-xs text-sky-300">
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
              <div className="border-b border-white/10 p-4">
                <strong>{active.contact_name || active.phone}</strong>
                <p className="text-xs text-slate-400">{active.phone}</p>
              </div>
              <div className="flex-1 space-y-2 overflow-auto p-4">
                {messages.map((msg) => (
                  <div
                    key={msg.id}
                    className={`max-w-[80%] rounded-2xl px-3 py-2 text-sm ${msg.direction === 'outbound' ? 'ml-auto bg-sky-500/20 text-sky-100' : 'bg-white/10 text-slate-200'}`}
                  >
                    <p>{msg.body}</p>
                    <span className="mt-1 block text-[10px] opacity-60">{formatDateTime(msg.created_at)}</span>
                  </div>
                ))}
                {messages.length === 0 && <p className="text-sm text-slate-500">Sem mensagens nesta conversa.</p>}
              </div>
              <div className="flex gap-2 border-t border-white/10 p-3">
                <Input
                  className="flex-1"
                  placeholder="Digite sua mensagem..."
                  value={body}
                  onChange={(e) => setBody(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && !e.shiftKey && (e.preventDefault(), send())}
                />
                <Button onClick={send}><Send size={16} /></Button>
              </div>
            </>
          )}
        </Card>
      </div>
    </>
  );
}
