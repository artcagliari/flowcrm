import { useCallback, useEffect, useRef, useState } from 'react';
import clientsApi from '../../api/clients';
import { globalSearch } from '../../api/search';

export default function ClientSelect({
  label = 'Cliente',
  value,
  onChange,
  onSelect,
  required,
  placeholder = 'Buscar cliente pelo nome...',
}) {
  const [query, setQuery] = useState('');
  const [selected, setSelected] = useState(null);
  const [results, setResults] = useState(null);
  const [open, setOpen] = useState(false);
  const box = useRef(null);

  const pick = useCallback((client) => {
    setSelected(client);
    setQuery('');
    setOpen(false);
    setResults(null);
    onChange?.(String(client.id));
    onSelect?.(client);
  }, [onChange, onSelect]);

  useEffect(() => {
    if (!value) {
      setSelected(null);
      return;
    }
    if (selected && String(selected.id) === String(value)) return;

    clientsApi.get(value).then((client) => {
      setSelected({ id: client.id, name: client.name });
      setQuery('');
    }).catch(() => setSelected(null));
  }, [value, selected]);

  useEffect(() => {
    const term = query.trim();
    if (term.length < 2) {
      setResults(null);
      return;
    }

    const timer = setTimeout(async () => {
      try {
        const data = await globalSearch(term);
        const clients = (data.clients || []).map((client) => ({ id: client.id, name: client.name }));
        setResults(clients);
        if (clients.length === 1) pick(clients[0]);
      } catch {
        setResults([]);
      }
    }, 300);

    return () => clearTimeout(timer);
  }, [query, pick]);

  useEffect(() => {
    function close(event) {
      if (!box.current?.contains(event.target)) setOpen(false);
    }
    window.addEventListener('click', close);
    return () => window.removeEventListener('click', close);
  }, []);

  function clear() {
    setSelected(null);
    setQuery('');
    onChange?.('');
    onSelect?.(null);
  }

  return (
    <div ref={box} className="relative">
      <label className="field">
        <span>{label}{required ? ' *' : ''}</span>
        <input
          value={selected ? selected.name : query}
          onChange={(event) => {
            if (selected) clear();
            setQuery(event.target.value);
            setOpen(true);
          }}
          onFocus={() => setOpen(true)}
          placeholder={placeholder}
          required={required && !selected}
        />
      </label>
      {selected && (
        <button type="button" onClick={clear} className="absolute right-3 top-9 text-xs text-sky-300">
          limpar
        </button>
      )}
      {open && results && !selected && (
        <div className="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-2xl border border-white/10 bg-[#080b16]/95 p-1 shadow-2xl">
          {results.length === 0 && <p className="p-3 text-sm text-slate-400">Nenhum cliente encontrado.</p>}
          {results.map((client) => (
            <button
              key={client.id}
              type="button"
              onClick={() => pick(client)}
              className="flex w-full rounded-xl p-2 text-left text-sm text-slate-100 hover:bg-white/10"
            >
              {client.name}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
