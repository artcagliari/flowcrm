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
import { getWhatsappSettings, saveWhatsappSettings, testWhatsapp } from '../api/whatsapp';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
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
        <WhatsappCard />

        <Card>
          <h3 className="mb-3 font-semibold">API</h3>
          <a className="text-sky-300" href={getOpenApiUrl()} target="_blank" rel="noreferrer">Abrir documentacao openapi.json</a>
        </Card>
      </section>
    </>
  );
}

function WhatsappCard() {
  const [settings, setSettings] = useState({ provider: 'log', is_active: false, api_version: 'v19.0' });
  const [testPhone, setTestPhone] = useState('');
  const [feedback, setFeedback] = useState('');
  const [error, setError] = useState('');

  const load = () => getWhatsappSettings().then((data) => setSettings((prev) => ({ ...prev, ...data })));
  useEffect(() => { load(); }, []);

  const set = (key, value) => setSettings((prev) => ({ ...prev, [key]: value }));

  async function save(event) {
    event.preventDefault();
    setError('');
    setFeedback('');
    try {
      const payload = {
        provider: settings.provider,
        is_active: settings.is_active,
        base_url: settings.base_url || '',
        instance: settings.instance || '',
        api_version: settings.api_version || 'v19.0',
        phone_number_id: settings.phone_number_id || '',
      };
      if (settings.api_key) payload.api_key = settings.api_key;
      if (settings.token) payload.token = settings.token;
      await saveWhatsappSettings(payload);
      setFeedback('Configuracao do WhatsApp salva.');
      await load();
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel salvar.').message);
    }
  }

  async function runTest() {
    setError('');
    setFeedback('');
    try {
      const result = await testWhatsapp(testPhone);
      setFeedback(result.message || 'Teste enviado.');
    } catch (err) {
      setError(handleApiError(err, 'Falha no teste.').message);
    }
  }

  return (
    <Card>
      <h3 className="mb-1 font-semibold">WhatsApp</h3>
      <p className="mb-3 text-sm text-slate-400">
        Conecte um provider para enviar e receber mensagens direto no CRM. No modo <strong>log</strong>, as mensagens sao registradas mas nao entregues (ideal para testes).
      </p>

      <form className="grid gap-3" onSubmit={save}>
        <Select label="Provider" value={settings.provider} onChange={(e) => set('provider', e.target.value)} options={[
          { value: 'log', label: 'Log (desenvolvimento)' },
          { value: 'evolution', label: 'Evolution API' },
          { value: 'meta', label: 'Meta Cloud API' },
        ]} />

        {settings.provider === 'evolution' && (
          <>
            <Input label="Base URL" value={settings.base_url || ''} onChange={(e) => set('base_url', e.target.value)} placeholder="https://evolution.suaempresa.com" />
            <Input label="Instancia" value={settings.instance || ''} onChange={(e) => set('instance', e.target.value)} placeholder="minha-instancia" />
            <Input label="API Key" type="password" value={settings.api_key || ''} onChange={(e) => set('api_key', e.target.value)} placeholder={settings.has_api_key ? '•••••• (salvo)' : ''} />
          </>
        )}

        {settings.provider === 'meta' && (
          <>
            <Input label="Phone Number ID" value={settings.phone_number_id || ''} onChange={(e) => set('phone_number_id', e.target.value)} />
            <Input label="Token" type="password" value={settings.token || ''} onChange={(e) => set('token', e.target.value)} placeholder={settings.has_token ? '•••••• (salvo)' : ''} />
            <Input label="API Version" value={settings.api_version || 'v19.0'} onChange={(e) => set('api_version', e.target.value)} />
          </>
        )}

        <label className="flex items-center gap-2 text-sm text-slate-300">
          <input type="checkbox" checked={Boolean(settings.is_active)} onChange={(e) => set('is_active', e.target.checked)} />
          Ativar integracao
        </label>

        {settings.webhook_url && (
          <div className="rounded-2xl border border-white/10 bg-white/5 p-3 text-xs text-slate-400">
            <p className="mb-2 font-medium text-slate-300">Configuracao do webhook (Meta)</p>
            <p className="mb-1">Callback URL (cole na Meta — <strong>sem</strong> query params):</p>
            <code className="mb-3 block break-all text-sky-300">{settings.webhook_url}</code>
            <p className="mb-1">Verify Token (campo separado na Meta, igual ao <code>WHATSAPP_WEBHOOK_TOKEN</code> do .env do servidor):</p>
            <code className="mb-3 block break-all text-sky-300">
              {settings.verify_token_configured ? 'Configurado no servidor (.env)' : 'Defina WHATSAPP_WEBHOOK_TOKEN no backend/.env'}
            </code>
            <p className="mb-1">App Secret (valida POST via header <code>X-Hub-Signature-256</code>):</p>
            <code className="block break-all text-sky-300">
              {settings.meta_app_secret_configured ? 'WHATSAPP_META_APP_SECRET configurado' : 'Defina WHATSAPP_META_APP_SECRET no backend/.env'}
            </code>
            <p className="mt-2 text-slate-500">Em producao, WHATSAPP_META_APP_SECRET e obrigatorio. PHP converte hub.mode em hub_mode no GET (nao e o Laravel).</p>
          </div>
        )}

        <Button type="submit" variant="secondary">Salvar configuracao</Button>
      </form>

      <div className="mt-4 grid gap-2 border-t border-white/10 pt-4">
        <Input label="Testar envio (numero)" value={testPhone} onChange={(e) => setTestPhone(e.target.value)} placeholder="(11) 99999-9999" />
        <Button type="button" variant="ghost" onClick={runTest} disabled={!testPhone.trim()}>Testar conexao</Button>
      </div>

      {feedback && <p className="mt-3 rounded-2xl border border-green-400/20 bg-green-500/10 p-3 text-sm text-green-200">{feedback}</p>}
      {error && <p className="mt-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
    </Card>
  );
}
