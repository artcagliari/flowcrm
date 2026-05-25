import { useEffect, useState } from 'react';
import { getDashboard } from '../api/dashboard';

export function useDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  useEffect(() => { getDashboard().then(setData).finally(() => setLoading(false)); }, []);
  return { data, loading };
}
