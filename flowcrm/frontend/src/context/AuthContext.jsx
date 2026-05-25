import { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import * as authApi from '../api/auth';

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [company, setCompany] = useState(null);
  const [loading, setLoading] = useState(Boolean(localStorage.getItem('flowcrm_token')));

  const applySession = useCallback((session) => {
    localStorage.setItem('flowcrm_token', session.token);
    if (session.company?.id) localStorage.setItem('flowcrm_company_id', session.company.id);
    setUser(session.user);
    setCompany(session.company);
  }, []);

  useEffect(() => {
    if (!localStorage.getItem('flowcrm_token')) return;
    authApi.me()
      .then((data) => {
        setUser(data.user);
        setCompany(data.company);
        if (data.company?.id) localStorage.setItem('flowcrm_company_id', data.company.id);
      })
      .finally(() => setLoading(false));
  }, []);

  async function login(payload) {
    const session = await authApi.login(payload);
    applySession(session);
  }

  async function register(payload) {
    const session = await authApi.register(payload);
    applySession(session);
  }

  async function logout() {
    try { await authApi.logout(); } catch { /* ignore network logout failures */ }
    localStorage.removeItem('flowcrm_token');
    localStorage.removeItem('flowcrm_company_id');
    setUser(null);
    setCompany(null);
  }

  const value = useMemo(() => ({ user, company, loading, login, register, logout, authenticated: Boolean(user) }), [user, company, loading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
