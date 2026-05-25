import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import useAuth from '../../hooks/useAuth';
import { AuthShell } from './Login';

export default function Register() {
  const [form, setForm] = useState({ name: '', email: '', password: '', company_name: '' });
  const [error, setError] = useState('');
  const { register } = useAuth();
  const navigate = useNavigate();
  async function submit(e) {
    e.preventDefault();
    setError('');
    try { await register(form); navigate('/'); } catch { setError('Não foi possível criar a conta. Revise os campos.'); }
  }
  return <AuthShell title="Criar conta" error={error} onSubmit={submit}>{['name', 'email', 'company_name'].map((key) => <Input key={key} label={{ name: 'Nome', email: 'E-mail', company_name: 'Empresa' }[key]} value={form[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} />)}<Input label="Senha" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} /><Button className="w-full">Criar conta</Button><Link className="text-sm text-sky-300" to="/login">Já tenho conta</Link></AuthShell>;
}
