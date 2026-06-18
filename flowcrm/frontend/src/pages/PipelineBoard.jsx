import { Link } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { GripVertical, Settings, Target, UserPlus } from 'lucide-react';
import leadsApi from '../api/leads';
import { updateDeal } from '../api/deals';
import { getPipelineBoard } from '../api/pipelines';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import useProfessionMode from '../hooks/useProfessionMode';
import { formatCurrency } from '../utils/formatCurrency';

export default function PipelineBoard() {
  const { config } = useProfessionMode();
  const [board, setBoard] = useState(null);
  const [dragItem, setDragItem] = useState(null);
  const [moving, setMoving] = useState(false);

  const load = () => getPipelineBoard().then(setBoard);
  useEffect(() => { load(); }, []);

  async function moveItem(stageId) {
    if (!dragItem || dragItem.stageId === stageId) return;
    setMoving(true);
    try {
      if (dragItem.kind === 'lead') {
        await leadsApi.update(dragItem.id, { lead_stage_id: stageId });
      } else {
        await updateDeal(dragItem.id, { lead_stage_id: stageId });
      }
      await load();
    } finally {
      setDragItem(null);
      setMoving(false);
    }
  }

  if (!board) {
    return <p className="text-slate-400">Carregando pipeline...</p>;
  }

  const { columns = [], summary = {}, pipeline } = board;

  return (
    <>
      <PageHeader
        title={config.pipelineLabel}
        subtitle="Arraste leads e oportunidades entre etapas do funil."
        action={(
          <div className="flex gap-2">
            <Button variant="secondary" onClick={load} disabled={moving}>Atualizar</Button>
            <Link to="/pipeline/settings"><Button variant="ghost"><Settings size={16} /> Configurar etapas</Button></Link>
          </div>
        )}
      />

      <section className="mb-4 grid gap-3 md:grid-cols-4">
        <MiniStat label="Leads no funil" value={summary.total_leads || 0} />
        <MiniStat label="Oportunidades abertas" value={summary.total_deals || 0} />
        <MiniStat label="Valor no pipeline" value={formatCurrency(summary.pipeline_value || 0)} />
        <MiniStat label="Forecast ponderado" value={formatCurrency(summary.weighted_forecast || 0)} highlight />
      </section>

      <div className="flex gap-4 overflow-x-auto pb-4">
        {columns.map((column) => (
          <div
            key={column.id}
            className="min-w-[280px] max-w-[320px] flex-1"
            onDragOver={(e) => e.preventDefault()}
            onDrop={() => moveItem(column.id)}
          >
            <div className="mb-3 flex items-center justify-between gap-2">
              <div className="flex items-center gap-2">
                <span className="h-3 w-3 rounded-full" style={{ background: column.color || '#4F8CFF' }} />
                <strong className="text-sm">{column.name}</strong>
                <span className="text-xs text-slate-500">({column.totals.leads + column.totals.deals})</span>
              </div>
              {column.totals.value > 0 && (
                <span className="text-xs text-slate-400">{formatCurrency(column.totals.weighted)}</span>
              )}
            </div>

            <div className={`grid min-h-[420px] gap-2 rounded-[22px] border border-white/10 bg-white/[0.03] p-2 ${moving ? 'opacity-70' : ''}`}>
              {column.leads.map((lead) => (
                <BoardCard
                  key={`lead-${lead.id}`}
                  draggable={!moving}
                  onDragStart={() => setDragItem({ kind: 'lead', id: lead.id, stageId: column.id })}
                  icon={UserPlus}
                  title={lead.name}
                  subtitle={lead.interest || lead.phone || 'Lead'}
                  meta={lead.temperature ? `${lead.temperature}${lead.estimated_value ? ` · ${formatCurrency(lead.estimated_value)}` : ''}` : null}
                  href={`/leads/${lead.id}`}
                />
              ))}
              {column.deals.map((deal) => (
                <BoardCard
                  key={`deal-${deal.id}`}
                  draggable={!moving}
                  onDragStart={() => setDragItem({ kind: 'deal', id: deal.id, stageId: column.id })}
                  icon={Target}
                  title={deal.title}
                  subtitle={deal.client?.name || deal.lead?.name || 'Oportunidade'}
                  meta={`${formatCurrency(deal.value)} · ${deal.probability}%`}
                  href="/deals"
                  accent="from-emerald-500/20 to-teal-500/10"
                />
              ))}
              {column.leads.length === 0 && column.deals.length === 0 && (
                <p className="p-4 text-center text-xs text-slate-500">Arraste itens para esta etapa</p>
              )}
            </div>
          </div>
        ))}
      </div>

      {pipeline && (
        <p className="text-xs text-slate-500">Pipeline ativo: {pipeline.name}</p>
      )}
    </>
  );
}

function MiniStat({ label, value, highlight }) {
  return (
    <Card className={highlight ? 'border-sky-400/30 bg-sky-500/5' : ''}>
      <span className="text-xs text-slate-500">{label}</span>
      <strong className="mt-1 block text-lg">{value}</strong>
    </Card>
  );
}

function BoardCard({ title, subtitle, meta, href, icon: Icon, draggable, onDragStart, accent = 'from-blue-500/15 to-indigo-500/5' }) {
  return (
    <div
      draggable={draggable}
      onDragStart={onDragStart}
      className={`cursor-grab rounded-2xl border border-white/10 bg-gradient-to-br ${accent} p-3 active:cursor-grabbing`}
    >
      <div className="flex items-start gap-2">
        <GripVertical size={14} className="mt-1 shrink-0 text-slate-500" />
        <div className="min-w-0 flex-1">
          <Link to={href} className="block truncate font-medium text-sky-200 hover:text-sky-100">{title}</Link>
          <p className="truncate text-xs text-slate-400">{subtitle}</p>
          {meta && <p className="mt-1 text-xs capitalize text-slate-500">{meta}</p>}
        </div>
        <Icon size={14} className="shrink-0 text-slate-500" />
      </div>
    </div>
  );
}
