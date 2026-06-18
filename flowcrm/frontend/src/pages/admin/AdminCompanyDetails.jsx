import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { activateAdminCompany, getAdminCompany, resetAdminCompanyPassword, suspendAdminCompany } from '../../api/admin';
import PageHeader from '../../components/shared/PageHeader';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import Input from '../../components/ui/Input';
import { formatDate } from '../../utils/formatDate';

export default function AdminCompanyDetails() {
  const { id } = useParams();
  const [company, setCompany] = useState(null);
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  async function load() { setCompany(await getAdminCompany(id)); }
  useEffect(() => { load(); }, [id]);
  if (!company) return null;
  const owner = company.users?.find((user) => user.pivot?.is_owner) || company.users?.[0];

  async function resetPassword() {
    await resetAdminCompanyPassword(id, { password, password_confirmation: password });
    setPassword('');
    setMessage('Senha redefinida com sucesso.');
  }

  async function toggleStatus() {
    if (company.status === 'suspended') await activateAdminCompany(id);
    else await suspendAdminCompany(id);
    await load();
  }

  return (
    <>
      <PageHeader title={company.name} subtitle="Dados da empresa cliente, administrador principal e status do acesso.">
        <Link to={`/admin/companies/${id}/edit`}><Button variant="secondary">Editar</Button></Link>
        <Button variant="secondary" onClick={toggleStatus}>{company.status === 'suspended' ? 'Ativar' : 'Suspender'}</Button>
      </PageHeader>
      {message && <p className="mb-4 rounded-2xl border border-sky-300/20 bg-sky-400/10 p-3 text-sm text-sky-100">{message}</p>}
      <div className="grid gap-4 xl:grid-cols-[1.1fr_.9fr]">
        <Card>
          <h2 className="mb-3 text-base font-bold">Dados da empresa</h2>
          <div className="grid gap-3 text-sm md:grid-cols-2">
            <Info label="Status" value={<Badge>{company.status}</Badge>} />
            <Info label="Perfil" value="Empresa" />
            <Info label="Tipo" value={company.type} />
            <Info label="Plano" value={company.plan_name || '-'} />
            <Info label="Usuarios" value={company.users_count || company.users?.length || 0} />
            <Info label="Inicio" value={formatDate(company.starts_at)} />
            <Info label="Vencimento" value={formatDate(company.expires_at)} />
            <Info label="Cidade" value={company.city || '-'} />
            <Info label="Estado" value={company.state || '-'} />
          </div>
          {company.notes && <p className="mt-4 rounded-2xl border border-white/10 bg-white/5 p-3 text-sm text-slate-300">{company.notes}</p>}
        </Card>
        <Card>
          <h2 className="mb-3 text-base font-bold">Administrador principal</h2>
          <p className="text-sm text-slate-300">{owner?.name || '-'}</p>
          <p className="text-sm text-slate-400">{owner?.email || '-'}</p>
          <div className="mt-4 grid gap-3">
            <Input label="Nova senha" type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
            <Button onClick={resetPassword} disabled={!password}>Redefinir senha</Button>
          </div>
        </Card>
      </div>
    </>
  );
}

function Info({ label, value }) {
  return <div className="rounded-2xl border border-white/10 bg-white/5 p-3"><span className="block text-xs text-slate-500">{label}</span><strong className="text-slate-100">{value}</strong></div>;
}
