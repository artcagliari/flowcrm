import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import useAuth from '../../hooks/useAuth';

export default function Login() {
  const [form, setForm] = useState({ email: 'admin@crm.com', password: 'password' });
  const [error, setError] = useState('');
  const { login } = useAuth();
  const navigate = useNavigate();

  async function submit(e) {
    e.preventDefault();
    setError('');
    try {
      const session = await login(form);
      navigate(session.user?.role === 'super_admin' ? '/admin' : '/dashboard', { replace: true });
    } catch (err) {
      setError(err.response?.data?.message || 'Nao foi possivel entrar. Verifique as credenciais.');
    }
  }

  return (
    <AuthShell title="Entrar no CRM" error={error} onSubmit={submit}>
      <Input label="E-mail" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
      <Input label="Senha" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
      <Button className="w-full">Entrar</Button>
      <p className="text-sm text-slate-400">Seu acesso e criado pelo administrador da plataforma.</p>
    </AuthShell>
  );
}

export function AuthShell({ title, error, onSubmit, children }) {
  return (
    <main className="grid min-h-screen place-items-center p-4">
      <form onSubmit={onSubmit} className="glass w-full max-w-md rounded-[30px] p-7">
        <div className="mb-6 flex items-center gap-3">
          <div className="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-300 font-extrabold">C</div>
          <div><strong>CRM</strong><p className="text-xs text-slate-400">SaaS multiempresa</p></div>
        </div>
        <h1 className="text-3xl font-extrabold">{title}</h1>
        <p className="mt-2 text-sm text-slate-400">Entre com o e-mail e senha fornecidos para acessar seu CRM.</p>
        {error && <p className="mt-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
        <div className="mt-6 grid gap-3">{children}</div>
      </form>
    </main>
  );
}
