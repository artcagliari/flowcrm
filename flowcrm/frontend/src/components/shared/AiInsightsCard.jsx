import { Sparkles, Target, Zap } from 'lucide-react';
import { useEffect, useState } from 'react';
import { getClientInsights, getLeadInsights } from '../../api/insights';
import Button from '../ui/Button';
import Card from '../ui/Card';
import { formatCurrency } from '../../utils/formatCurrency';

export default function AiInsightsCard({ type, entityId, onUseMessage }) {
  const [insights, setInsights] = useState(null);
  const [loading, setLoading] = useState(true);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (!entityId) return;
    setLoading(true);
    const loader = type === 'client' ? getClientInsights : getLeadInsights;
    loader(entityId)
      .then(setInsights)
      .catch(() => setInsights(null))
      .finally(() => setLoading(false));
  }, [type, entityId]);

  if (loading) {
    return (
      <Card className="border-violet-400/20 bg-violet-500/5">
        <p className="text-sm text-slate-400">Gerando insights comerciais...</p>
      </Card>
    );
  }

  if (!insights) return null;

  async function copyMessage() {
    if (!insights?.suggested_message) return;
    await navigator.clipboard.writeText(insights.suggested_message);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
    onUseMessage?.(insights.suggested_message);
  }

  return (
    <Card className="border-violet-400/20 bg-gradient-to-br from-violet-500/10 to-indigo-500/5">
      <div className="mb-3 flex items-center gap-2">
        <div className="grid h-9 w-9 place-items-center rounded-xl bg-violet-500/20 text-violet-200">
          <Sparkles size={18} />
        </div>
        <div>
          <strong className="block">Assistente comercial</strong>
          <span className="text-xs text-slate-400">IA por regras · Plano Pro</span>
        </div>
      </div>

      <p className="mb-3 text-sm leading-relaxed text-slate-200">{insights.summary}</p>

      <div className="mb-3 flex items-start gap-2 rounded-2xl border border-amber-400/20 bg-amber-500/10 p-3">
        <Zap size={16} className="mt-0.5 shrink-0 text-amber-300" />
        <div>
          <span className="text-xs uppercase tracking-wide text-amber-200/80">Proxima acao</span>
          <p className="text-sm font-medium text-amber-100">{insights.next_action}</p>
        </div>
      </div>

      {insights.signals && (
        <div className="mb-3 flex flex-wrap gap-2 text-xs">
          {insights.signals.open_deals > 0 && (
            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-300">
              <Target size={12} className="mr-1 inline" />
              {insights.signals.open_deals} deal(s) · {formatCurrency(insights.signals.pipeline_value)}
            </span>
          )}
          {insights.signals.weighted_forecast > 0 && (
            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-300">
              Forecast: {formatCurrency(insights.signals.weighted_forecast)}
            </span>
          )}
          {insights.signals.temperature && (
            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 capitalize text-slate-300">
              {insights.signals.temperature}
            </span>
          )}
        </div>
      )}

      <div className="rounded-2xl border border-white/10 bg-black/20 p-3">
        <span className="mb-1 block text-xs text-slate-500">Mensagem sugerida (WhatsApp)</span>
        <p className="text-sm text-slate-300">{insights.suggested_message}</p>
        <Button size="sm" variant="secondary" className="mt-3" onClick={copyMessage}>
          {copied ? 'Copiado!' : 'Copiar mensagem'}
        </Button>
      </div>
    </Card>
  );
}
