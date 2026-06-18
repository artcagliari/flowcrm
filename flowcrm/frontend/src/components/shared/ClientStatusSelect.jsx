import { useEffect, useState } from 'react';
import clientsApi from '../../api/clients';
import Badge from '../ui/Badge';
import { clientStatusOptions } from '../../utils/constants';

export function clientStatusLabel(value) {
  return clientStatusOptions.find((option) => option.value === value)?.label || value || '-';
}

export default function ClientStatusSelect({ clientId, value, onUpdated, variant = 'select' }) {
  const [status, setStatus] = useState(value || 'encaminhado');
  const [saving, setSaving] = useState(false);
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    setStatus(value || 'encaminhado');
  }, [value]);

  async function change(event) {
    const next = event.target.value;
    if (!next || next === status) return;

    const previous = status;
    setStatus(next);
    setSaving(true);
    setFailed(false);

    try {
      await clientsApi.update(clientId, { status: next });
      onUpdated?.(next);
    } catch {
      setStatus(previous);
      setFailed(true);
    } finally {
      setSaving(false);
    }
  }

  if (variant === 'badge') {
    return <Badge>{clientStatusLabel(status)}</Badge>;
  }

  const options = clientStatusOptions.some((option) => option.value === status)
    ? clientStatusOptions
    : [{ value: status, label: clientStatusLabel(status) }, ...clientStatusOptions];

  const select = (
    <select
      value={status}
      onChange={change}
      disabled={saving}
      onClick={(event) => event.stopPropagation()}
      title={failed ? 'Nao foi possivel salvar o status. Tente novamente.' : undefined}
      className={`min-h-9 w-full rounded-xl border bg-white/5 px-2 py-1 text-sm text-slate-100 outline-none transition focus:border-[color:var(--primary)] disabled:opacity-60 ${failed ? 'border-red-400/60' : 'border-white/10'}`}
    >
      {options.map((option) => (
        <option key={option.value} value={option.value} className="bg-slate-900 text-slate-100">
          {option.label}
        </option>
      ))}
    </select>
  );

  if (variant === 'labeled') {
    return (
      <label className="grid gap-1">
        <span className="text-xs font-bold uppercase tracking-wide text-slate-400">Status do atendimento</span>
        {select}
      </label>
    );
  }

  return select;
}
