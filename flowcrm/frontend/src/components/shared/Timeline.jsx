import { useEffect, useState } from 'react';
import { getTimeline } from '../../api/advanced';
import EmptyState from '../ui/EmptyState';
import { formatDateTime } from '../../utils/formatDate';

export default function Timeline({ type, id }) {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!id) return;
    setLoading(true);
    getTimeline(type, id).then(setItems).finally(() => setLoading(false));
  }, [type, id]);

  if (loading) return <p className="text-sm text-slate-400">Carregando timeline...</p>;
  if (!items.length) return <EmptyState title="Sem historico" description="Nenhuma interacao registrada ainda." />;

  return (
    <div className="grid gap-3">
      {items.map((item) => (
        <div key={`${item.type}-${item.id}`} className="rounded-2xl border border-white/10 bg-white/5 p-3">
          <div className="flex items-center justify-between gap-3">
            <strong className="text-sm">{item.title}</strong>
            <span className="text-xs text-slate-400">{formatDateTime(item.occurred_at)}</span>
          </div>
          <p className="mt-1 text-sm text-slate-300">{item.description}</p>
          <span className="mt-2 inline-block rounded-full bg-white/10 px-2 py-0.5 text-xs uppercase text-slate-400">{item.type}</span>
        </div>
      ))}
    </div>
  );
}
