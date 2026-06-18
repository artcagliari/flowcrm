import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import {
  connectGoogleCalendar,
  disconnectGoogleCalendar,
  exportClients,
  exportLeads,
  getOpenApiUrl,
  importClients,
  importLeads,
  listIntegrations,
  updateGoogleCalendar,
} from '../api/advanced';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import { handleApiError } from '../utils/handleApiError';

function downloadBlob(blob, filename) {
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  window.URL.revokeObjectURL(url);
}

export default function Integrations() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [integrations, setIntegrations] = useState([]);
  const [calendarId, setCalendarId] = useState('primary');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [connectingGoogle, setConnectingGoogle] = useState(false);

  const googleIntegration = useMemo(
    () => integrations.find((item) => item.provider === 'google_calendar' && item.is_active),
    [integrations],
  );

  const load = async () => {
    const items = await listIntegrations();
    setIntegrations(items);
    const google = items.find((item) => item.provider === 'google_calendar');
    if (google?.credentials?.calendar_id) setCalendarId(google.credentials.calendar_id);
  };

  useEffect(() => { load(); }, []);

  useEffect(() => {
    const status = searchParams.get('google');
    if (!status) return;
    if (status === 'connected') {
      const synced = searchParams.get('synced');
      setError('');
      setMessage(synced ? `Google conectado. ${synced} compromisso(s) sincronizado(s) automaticamente.` : 'Google Agenda conectada. Novos compromissos serao enviados automaticamente.');
    }
    if (status === 'error') {
      setMessage('');
      setError(searchParams.get('message') || 'Nao foi possivel conectar com o Google.');
    }
    searchParams.delete('google');
    searchParams.delete('synced');
    searchParams.delete('message');
    setSearchParams(searchParams, { replace: true });
    load();
  }, [searchParams, setSearchParams]);

  async function connectGoogle() {
    setError('');
    setMessage('');
    setConnectingGoogle(true);

    try {
      const { url } = await connectGoogleCalendar();
      window.location.assign(url);
    } catch (err) {
      setConnectingGoogle(false);
      setError(handleApiError(err, 'Nao foi possivel iniciar a conexao com o Google.').message);
    }
  }

  return (
    <>
      <PageHeader title="Integracoes" subtitle="Google Agenda e importacao de dados." />
      {message && <p className="mb-4 rounded-2xl border border-green-400/20 bg-green-500/10 p-3 text-sm text-green-200">{message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <section className="grid gap-4 xl:grid-cols-2">
        <Card>
          <h3 className="mb-3 font-semibold">Google Agenda</h3>
          <p className="mb-4 text-sm text-slate-400">
            Ao conectar, compromissos futuros da Agenda do CRM sao enviados automaticamente para o Google.
            Novos agendamentos tambem vao direto, sem precisar clicar em sincronizar.
          </p>
          {googleIntegration ? (
            <div className="grid gap-3">
              <p className="rounded-2xl border border-green-400/20 bg-green-500/10 p-3 text-sm text-green-100">
                Conectado: {googleIntegration.credentials?.connected_email || 'Conta Google'}
              </p>
              <form className="grid gap-3" onSubmit={async (e) => { e.preventDefault(); await updateGoogleCalendar({ calendar_id: calendarId }); setMessage('Agenda atualizada.'); load(); }}>
                <Input label="Calendar ID" value={calendarId} onChange={(e) => setCalendarId(e.target.value)} placeholder="primary" />
                <div className="flex flex-wrap gap-2">
                  <Button type="submit" variant="secondary">Salvar agenda</Button>
                  <Button type="button" variant="secondary" onClick={async () => { await disconnectGoogleCalendar(); setMessage('Conta desconectada.'); load(); }}>Desconectar</Button>
                </div>
              </form>
            </div>
          ) : (
            <Button type="button" onClick={connectGoogle} disabled={connectingGoogle}>
              {connectingGoogle ? 'Conectando...' : 'Conectar com Google'}
            </Button>
          )}
        </Card>

        <Card>
          <h3 className="mb-3 font-semibold">Import / Export CSV</h3>
          <div className="flex flex-wrap gap-2">
            <Button onClick={() => exportClients().then((r) => downloadBlob(r.data, 'clientes.csv'))}>Exportar clientes</Button>
            <Button onClick={() => exportLeads().then((r) => downloadBlob(r.data, 'contatos.csv'))}>Exportar contatos</Button>
          </div>
          <div className="mt-4 grid gap-2">
            <label className="text-sm">Importar clientes<input type="file" accept=".csv" onChange={(e) => e.target.files?.[0] && importClients(e.target.files[0]).then(load)} /></label>
            <label className="text-sm">Importar contatos<input type="file" accept=".csv" onChange={(e) => e.target.files?.[0] && importLeads(e.target.files[0]).then(load)} /></label>
          </div>
        </Card>
        <Card>
          <h3 className="mb-3 font-semibold">API</h3>
          <a className="text-sky-300" href={getOpenApiUrl()} target="_blank" rel="noreferrer">Abrir documentacao openapi.json</a>
        </Card>
      </section>
    </>
  );
}
