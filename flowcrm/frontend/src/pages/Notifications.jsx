import { useEffect, useState } from 'react';
import { listNotifications, markAllNotificationsRead, markNotificationRead } from '../api/notifications';
import Card from '../components/ui/Card';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import EmptyState from '../components/ui/EmptyState';
import PageHeader from '../components/shared/PageHeader';
import { formatDateTime } from '../utils/formatDate';
import { handleApiError } from '../utils/handleApiError';

export default function Notifications() {
  const [notifications, setNotifications] = useState([]);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  async function load() {
    const data = await listNotifications({ per_page: 50 });
    setNotifications(data.data || data);
  }

  useEffect(() => { load(); }, []);

  async function read(id) {
    setError('');
    try {
      await markNotificationRead(id);
      setMessage('Operacao concluida.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel marcar como lida.').message);
    }
  }

  async function readAll() {
    setError('');
    try {
      await markAllNotificationsRead();
      setMessage('Operacao concluida.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel marcar todas como lidas.').message);
    }
  }

  return (
    <>
      <PageHeader title="Notificacoes" subtitle="Alertas internos da operacao.">
        <Button variant="secondary" onClick={readAll}>Marcar todas como lidas</Button>
      </PageHeader>
      {message && <p className="mb-4 inline-flex rounded-full border border-green-400/20 bg-green-500/10 px-4 py-2 text-sm text-green-200">{message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <Card>
        {notifications.length ? (
          <div className="grid gap-3">
            {notifications.map((item) => (
              <div className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3" key={item.id}>
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <strong>{item.title}</strong>
                    <Badge>{item.read_at ? 'lida' : 'nova'}</Badge>
                  </div>
                  <p className="text-sm text-slate-400">{item.message || item.body}</p>
                  <p className="text-xs text-slate-500">{formatDateTime(item.created_at)}</p>
                </div>
                {!item.read_at && <Button variant="secondary" onClick={() => read(item.id)}>Marcar como lida</Button>}
              </div>
            ))}
          </div>
        ) : <EmptyState title="Nenhuma notificacao" description="Avisos importantes aparecerao aqui." />}
      </Card>
    </>
  );
}
