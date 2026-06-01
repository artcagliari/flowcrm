import { useEffect, useState } from 'react';
import { getSettings, updateTheme } from '../api/settings';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import PageHeader from '../components/shared/PageHeader';
import { applyCompanyTheme } from '../utils/theme';
import { handleApiError } from '../utils/handleApiError';

export default function Settings() {
  const [company, setCompany] = useState(null);
  const [color, setColor] = useState('#4F8CFF');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    getSettings().then((data) => {
      setCompany(data.company);
      setColor(data.company?.primary_color || data.settings?.primary_color || '#4F8CFF');
      applyCompanyTheme(data.company?.primary_color || data.settings?.primary_color || '#4F8CFF');
    });
  }, []);

  async function saveTheme(event) {
    event.preventDefault();
    setSaving(true);
    setError('');
    setMessage('');
    try {
      const data = await updateTheme(color);
      applyCompanyTheme(data.primary_color);
      setMessage('Operacao concluida. Tema atualizado com sucesso.');
    } catch (err) {
      setError(handleApiError(err, 'Nao foi possivel salvar a cor.').message);
    } finally {
      setSaving(false);
    }
  }

  return (
    <>
      <PageHeader title="Configuracoes" subtitle="Ajuste dados da conta, empresa, perfil e preferencias do sistema." />
      {message && <p className="mb-4 inline-flex rounded-full border border-green-400/20 bg-green-500/10 px-4 py-2 text-sm text-green-200">{message}</p>}
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <div className="grid gap-4 xl:grid-cols-[.9fr_1.1fr]">
        <Card>
          <h2 className="mb-3 text-base font-bold">Dados da empresa</h2>
          <div className="grid gap-3">
            <Input label="Nome da empresa" value={company?.name || ''} readOnly />
            <Input label="Documento" value={company?.document || ''} readOnly />
            <Input label="E-mail" value={company?.email || ''} readOnly />
          </div>
        </Card>
        <Card>
          <form onSubmit={saveTheme} className="grid gap-4">
            <div>
              <h2 className="text-base font-bold">Cor principal</h2>
              <p className="text-sm text-slate-400">Esta cor altera botoes, destaques, links e foco dos campos.</p>
            </div>
            <div className="flex flex-wrap items-end gap-3">
              <label className="field min-w-40">
                <span>Cor</span>
                <input type="color" value={color} onChange={(event) => setColor(event.target.value)} />
              </label>
              <Input label="Hexadecimal" value={color} onChange={(event) => setColor(event.target.value)} />
              <span className="grid h-11 w-11 place-items-center rounded-2xl border border-white/10" style={{ background: color }} />
            </div>
            <Button disabled={saving}>{saving ? 'Salvando...' : 'Salvar cor'}</Button>
          </form>
        </Card>
      </div>
    </>
  );
}
