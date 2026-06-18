import { useEffect, useRef, useState } from 'react';
import { globalSearch } from '../../api/search';

const LABELS = { clients: 'Cliente', leads: 'Lead' };

// Searchable picker for clients and/or leads. Calls onSelect(item|null),
// where item is { type, id, name }. Parent maps type -> client_id / lead_id.
export default function EntityAutocomplete({ label = 'Vincular a', types = ['clients', 'leads'], value, onSelect, placeholder = 'Buscar cliente ou lead...' }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState(null);
  const [open, setOpen] = useState(false);
  const box = useRef(null);

  useEffect(() => {
    if (query.trim().length < 2) { setResults(null); return; }
    const timer = setTimeout(async () => {
      try {
        const data = await globalSearch(query);
        const items = types.flatMap((type) => (data[type] || []).map((item) => ({ type, id: item.id, name: item.name })));
        setResults(items);
        if (items.length === 1) {
          onSelect(items[0]);
          setOpen(false);
          setQuery('');
        }
      } catch { setResults([]); }
    }, 300);
    return () => clearTimeout(timer);
  }, [query, types]);

  useEffect(() => {
    function close(event) { if (!box.current?.contains(event.target)) setOpen(false); }
    window.addEventListener('click', close);
    return () => window.removeEventListener('click', close);
  }, []);

  return (
    <div ref={box} className="relative">
      <label className="field">
        <span>{label}</span>
        <input
          value={value ? value.name : query}
          onChange={(event) => { if (value) onSelect(null); setQuery(event.target.value); setOpen(true); }}
          onFocus={() => setOpen(true)}
          placeholder={placeholder}
        />
      </label>
      {value && <button type="button" onClick={() => { onSelect(null); setQuery(''); }} className="absolute right-3 top-9 text-xs text-sky-300">limpar</button>}
      {open && results && (
        <div className="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-2xl border border-white/10 bg-[#080b16]/95 p-1 shadow-2xl">
          {results.length === 0 && <p className="p-3 text-sm text-slate-400">Nenhum resultado.</p>}
          {results.map((item) => (
            <button key={`${item.type}-${item.id}`} type="button" onClick={() => { onSelect(item); setOpen(false); setQuery(''); }} className="flex w-full items-center justify-between rounded-xl p-2 text-left text-sm hover:bg-white/10">
              <span className="text-slate-100">{item.name}</span><span className="text-xs text-slate-500">{LABELS[item.type] || item.type}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
