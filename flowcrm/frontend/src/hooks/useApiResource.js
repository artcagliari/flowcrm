import { useEffect, useState } from 'react';

export function useApiResource(api, params = {}) {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  async function load() {
    setLoading(true);
    const data = await api.list(params);
    setItems(data.data || data);
    setLoading(false);
  }

  useEffect(() => { load(); }, []);

  return { items, setItems, loading, reload: load };
}
