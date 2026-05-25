import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import useAuth from '../../hooks/useAuth';

export default function Login() {
  const [form, setForm] = useState({ email: 'admin@flowcrm.test', password: 'password' });
  const [error, setError] = useState('');
  const { login } = useAuth();
  const navigate = useNavigate();
  async function submit(e) {
    e.preventDefault();
    setError('');
    try { await login(form); navigate('/'); } catch { setError('Não foi possível entrar. Verifique as credenciais.'); }
  }
  return <AuthShell title="Entrar no FlowCRM" error={error} onSubmit={submit}><Input label="E-mail" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} /><Input label="Senha" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} /><Button className="w-full">Entrar</Button><Link className="text-sm text-sky-300" to="/register">Criar uma conta</Link></AuthShell>;
}

export function AuthShell({ title, error, onSubmit, children }) {
  return <main className="grid min-h-screen place-items-center p-4"><form onSubmit={onSubmit} className="glass w-full max-w-md rounded-[30px] p-7"><div className="mb-6 flex items-center gap-3"><div className="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-300 font-extrabold">F</div><div><strong>FlowCRM</strong><p className="text-xs text-slate-400">CRM premium multiempresa</p></div></div><h1 className="text-3xl font-extrabold">{title}</h1>{error && <p className="mt-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}<div className="mt-6 grid gap-3">{children}</div></form></main>;
}
