import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { createAdminCompany } from '../../api/admin';
import PageHeader from '../../components/shared/PageHeader';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import Input from '../../components/ui/Input';
import Select from '../../components/ui/Select';
import Textarea from '../../components/ui/Textarea';

const initial = {
  company: { name: '', legal_name: '', document: '', email: '', phone: '', whatsapp: '', city: '', state: '', address: '', type: 'company', status: 'active', plan_name: '', max_users: '', starts_at: '', expires_at: '', notes: '' },
  admin: { name: '', email: '', password: '', password_confirmation: '', phone: '' },
};

export default function AdminCompanyForm() {
  const [form, setForm] = useState(initial);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  function setGroup(group, key, value) {
    setForm((current) => ({ ...current, [group]: { ...current[group], [key]: value } }));
  }

  async function submit(e) {
    e.preventDefault();
    setError('');
    try {
      await createAdminCompany(form);
      navigate('/admin/companies');
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(errors ? Object.values(errors).flat()[0] : err.response?.data?.message || 'Nao foi possivel criar a empresa.');
    }
  }

  return (
    <>
      <PageHeader title="Criar empresa" subtitle="Cadastre uma nova empresa cliente e gere o acesso do administrador responsavel." />
      {error && <p className="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-sm text-red-200">{error}</p>}
      <form onSubmit={submit} className="grid gap-4">
        <Card>
          <h2 className="mb-3 text-base font-bold">Dados da empresa</h2>
          <div className="grid gap-3 md:grid-cols-2">
            <Input label="Nome da empresa" value={form.company.name} onChange={(e) => setGroup('company', 'name', e.target.value)} required />
            <Input label="Razao social" value={form.company.legal_name} onChange={(e) => setGroup('company', 'legal_name', e.target.value)} />
            <Input label="CNPJ/Documento" value={form.company.document} onChange={(e) => setGroup('company', 'document', e.target.value)} />
            <Input label="E-mail da empresa" value={form.company.email} onChange={(e) => setGroup('company', 'email', e.target.value)} />
            <Input label="Telefone" value={form.company.phone} onChange={(e) => setGroup('company', 'phone', e.target.value)} />
            <Input label="WhatsApp" value={form.company.whatsapp} onChange={(e) => setGroup('company', 'whatsapp', e.target.value)} />
            <Input label="Cidade" value={form.company.city} onChange={(e) => setGroup('company', 'city', e.target.value)} />
            <Input label="Estado" value={form.company.state} onChange={(e) => setGroup('company', 'state', e.target.value)} />
            <Input label="Endereco" value={form.company.address} onChange={(e) => setGroup('company', 'address', e.target.value)} />
            <Select label="Tipo" value={form.company.type} onChange={(e) => setGroup('company', 'type', e.target.value)}><option value="company">Empresa</option><option value="autonomous">Autonomo</option></Select>
          </div>
        </Card>
        <Card>
          <h2 className="mb-3 text-base font-bold">Dados de acesso</h2>
          <div className="grid gap-3 md:grid-cols-2">
            <Input label="Nome do administrador" value={form.admin.name} onChange={(e) => setGroup('admin', 'name', e.target.value)} required />
            <Input label="E-mail de login" value={form.admin.email} onChange={(e) => setGroup('admin', 'email', e.target.value)} required />
            <Input label="Senha" type="password" value={form.admin.password} onChange={(e) => setGroup('admin', 'password', e.target.value)} required />
            <Input label="Confirmar senha" type="password" value={form.admin.password_confirmation} onChange={(e) => setGroup('admin', 'password_confirmation', e.target.value)} required />
            <Input label="Telefone" value={form.admin.phone} onChange={(e) => setGroup('admin', 'phone', e.target.value)} />
          </div>
        </Card>
        <Card>
          <h2 className="mb-3 text-base font-bold">Plano e status</h2>
          <div className="grid gap-3 md:grid-cols-2">
            <Input label="Plano" value={form.company.plan_name} onChange={(e) => setGroup('company', 'plan_name', e.target.value)} />
            <Input label="Maximo de usuarios" type="number" value={form.company.max_users} onChange={(e) => setGroup('company', 'max_users', e.target.value)} />
            <Input label="Inicio" type="date" value={form.company.starts_at} onChange={(e) => setGroup('company', 'starts_at', e.target.value)} />
            <Input label="Vencimento" type="date" value={form.company.expires_at} onChange={(e) => setGroup('company', 'expires_at', e.target.value)} />
            <Select label="Status" value={form.company.status} onChange={(e) => setGroup('company', 'status', e.target.value)}><option value="active">Ativa</option><option value="inactive">Inativa</option><option value="suspended">Suspensa</option></Select>
            <Textarea label="Observacoes internas" value={form.company.notes} onChange={(e) => setGroup('company', 'notes', e.target.value)} />
          </div>
        </Card>
        <div className="flex flex-wrap gap-2">
          <Button>Criar empresa e acesso</Button>
          <Link to="/admin/companies"><Button type="button" variant="secondary">Cancelar</Button></Link>
        </div>
      </form>
    </>
  );
}
