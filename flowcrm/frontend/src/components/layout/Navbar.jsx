import { Bell, Check, FileText, Loader2, Menu, Plus, Search } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { globalSearch } from '../../api/search';
import { listNotifications, markAllNotificationsRead, markNotificationRead } from '../../api/notifications';
import { handleApiError } from '../../utils/handleApiError';
import Button from '../ui/Button';

const groups = [
  ['clients', 'Cliente', (item) => `/clients/${item.id}`, (item) => item.name, (item) => item.phone || item.email || item.status],
  ['leads', 'Lead', (item) => `/leads/${item.id}`, (item) => item.name, (item) => item.phone || item.email || item.temperature || item.status],
  ['tasks', 'Tarefa', (item) => item.client_id ? `/clients/${item.client_id}` : '/tasks', (item) => item.title, (item) => item.client?.name || item.status],
  ['appointments', 'Agenda', (item) => item.client_id ? `/clients/${item.client_id}` : '/appointments', (item) => item.title, (item) => item.client?.name || item.status],
  ['payments', 'Pagamento', (item) => item.client_id ? `/clients/${item.client_id}` : '/finance', (item) => item.description, (item) => item.client?.name || item.status],
  ['documents', 'Documento', (item) => item.client_id ? `/clients/${item.client_id}` : '/documents', (item) => item.name, (item) => item.client?.name || item.category],
  ['notes', 'Nota', (item) => item.client_id ? `/clients/${item.client_id}` : '/clients', (item) => item.content, (item) => item.client?.name || item.type],
  ['users', 'Usuario', () => '/users', (item) => item.name, (item) => item.email],
];

export default function Navbar({ onMenu }) {
  const navigate = useNavigate();
  const [query, setQuery] = useState('');
  const [results, setResults] = useState(null);
  const [searching, setSearching] = useState(false);
  const [searchError, setSearchError] = useState('');
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const box = useRef(null);

  const flatResults = useMemo(() => groups.flatMap(([key, label, href, title, subtitle]) => (results?.[key] || []).map((item) => ({ key, label, href: href(item), title: title(item), subtitle: subtitle(item), id: item.id }))), [results]);
  const unread = notifications.filter((item) => !item.read_at).length;

  useEffect(() => {
    if (query.trim().length < 2) {
      setResults(null);
      return;
    }
    const timer = setTimeout(async () => {
      setSearching(true);
      setSearchError('');
      try { setResults(await globalSearch(query)); } catch (error) { setSearchError(handleApiError(error, 'Falha ao buscar.').message); }
      finally { setSearching(false); }
    }, 350);
    return () => clearTimeout(timer);
  }, [query]);

  useEffect(() => {
    function close(event) {
      if (!box.current?.contains(event.target)) setResults(null);
    }
    window.addEventListener('click', close);
    return () => window.removeEventListener('click', close);
  }, []);

  async function loadNotifications() {
    const data = await listNotifications({ per_page: 8 });
    setNotifications(data.data || data);
  }

  useEffect(() => { loadNotifications().catch(() => {}); }, []);

  async function openNotifications() {
    setNotificationsOpen((value) => !value);
    await loadNotifications();
  }

  async function read(id) {
    await markNotificationRead(id);
    await loadNotifications();
  }

  async function readAll() {
    await markAllNotificationsRead();
    await loadNotifications();
  }

  return (
    <header className="glass sticky top-4 z-30 flex min-h-16 items-center gap-3 rounded-[24px] p-3">
      <button className="rounded-2xl border border-white/10 bg-white/5 p-3 lg:hidden" onClick={onMenu}><Menu size={18} /></button>
      <div ref={box} className="relative flex h-11 flex-1 items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 text-slate-400">
        <Search size={17} />
        <input value={query} onChange={(event) => setQuery(event.target.value)} className="w-full bg-transparent text-slate-100 outline-none" placeholder="Buscar clientes, tarefas, documentos..." />
        {searching && <Loader2 className="animate-spin" size={16} />}
        {(results || searchError) && (
          <div className="absolute left-0 right-0 top-14 z-50 max-h-96 overflow-auto rounded-2xl border border-white/10 bg-[#080b16]/95 p-2 shadow-2xl backdrop-blur-xl">
            {searchError && <p className="p-3 text-sm text-red-200">{searchError}</p>}
            {!searchError && flatResults.length === 0 && <p className="p-3 text-sm text-slate-400">Nenhum resultado encontrado.</p>}
            {flatResults.map((item) => (
              <button key={`${item.key}-${item.id}`} onClick={() => { setQuery(''); setResults(null); navigate(item.href); }} className="flex w-full items-start gap-3 rounded-xl p-3 text-left hover:bg-white/10">
                <FileText className="mt-1 text-sky-300" size={16} />
                <span><strong className="block text-sm text-white">{item.label}: {item.title}</strong><small className="text-slate-400">{item.subtitle}</small></span>
              </button>
            ))}
          </div>
        )}
      </div>
      <div className="relative">
        <button onClick={openNotifications} className="relative rounded-2xl border border-white/10 bg-white/5 p-3">
          <Bell size={18} /> {unread > 0 && <span className="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold">{unread}</span>}
        </button>
        {notificationsOpen && (
          <div className="absolute right-0 top-14 z-50 w-80 rounded-2xl border border-white/10 bg-[#080b16]/95 p-3 shadow-2xl backdrop-blur-xl">
            <div className="mb-2 flex items-center justify-between">
              <strong>Notificacoes</strong>
              <button className="text-xs text-sky-300" onClick={readAll}>Marcar todas</button>
            </div>
            <div className="grid max-h-80 gap-2 overflow-auto">
              {notifications.length === 0 && <p className="p-3 text-sm text-slate-400">Nenhuma notificacao.</p>}
              {notifications.map((item) => (
                <div key={item.id} className="rounded-xl border border-white/10 bg-white/5 p-3">
                  <strong className="block text-sm">{item.title}</strong>
                  <p className="text-xs text-slate-400">{item.message || item.body}</p>
                  {!item.read_at && <button className="mt-2 inline-flex items-center gap-1 text-xs text-sky-300" onClick={() => read(item.id)}><Check size={13} /> Marcar como lida</button>}
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
      <Button onClick={() => navigate('/clients')}><Plus size={16} /> Novo</Button>
    </header>
  );
}
