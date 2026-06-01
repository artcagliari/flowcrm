import { Plus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { activateAdminCompany, listAdminCompanies, suspendAdminCompany } from '../../api/admin';
import PageHeader from '../../components/shared/PageHeader';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import Input from '../../components/ui/Input';
import Select from '../../components/ui/Select';
import Table from '../../components/ui/Table';
import { formatDate } from '../../utils/formatDate';

const statusLabels = { active: 'Ativa', inactive: 'Inativa', suspended: 'Suspensa' };

export default function AdminCompanies() {
  const [query, setQuery] = useState({ search: '', status: '' });
  const [companies, setCompanies] = useState([]);

  async function load() {
    const data = await listAdminCompanies(query);
    setCompanies(data.data || data);
  }

  useEffect(() => { load(); }, [JSON.stringify(query)]);

  async function suspend(id) { await suspendAdminCompany(id); load(); }
  async function activate(id) { await activateAdminCompany(id); load(); }

  return (
    <>
      <PageHeader title="Empresas" subtitle="Gerencie as empresas que utilizam o CRM.">
        <Link to="/admin/companies/new"><Button><Plus size={16} /> Nova empresa</Button></Link>
      </PageHeader>
      <Card className="mb-4">
        <div className="grid gap-3 md:grid-cols-3">
          <Input label="Buscar" value={query.search} onChange={(e) => setQuery({ ...query, search: e.target.value })} />
          <Select label="Status" value={query.status} onChange={(e) => setQuery({ ...query, status: e.target.value })}>
            <option value="">Todos</option>
            <option value="active">Ativa</option>
            <option value="inactive">Inativa</option>
            <option value="suspended">Suspensa</option>
          </Select>
        </div>
      </Card>
      <Card>
        {companies.length ? (
          <Table
            rows={companies}
            columns={[
              { key: 'name', label: 'Empresa' },
              { key: 'owner_name', label: 'Responsavel' },
              { key: 'email', label: 'E-mail' },
              { key: 'city', label: 'Cidade' },
              { key: 'status', label: 'Status', render: (row) => <Badge>{statusLabels[row.status] || row.status}</Badge> },
              { key: 'plan_name', label: 'Plano' },
              { key: 'created_at', label: 'Criada em', render: (row) => formatDate(row.created_at) },
            ]}
            renderActions={(row) => (
              <div className="flex flex-wrap gap-2">
                <Link to={`/admin/companies/${row.id}`}><Button variant="secondary">Ver</Button></Link>
                {row.status === 'suspended' ? <Button variant="secondary" onClick={() => activate(row.id)}>Ativar</Button> : <Button variant="secondary" onClick={() => suspend(row.id)}>Suspender</Button>}
              </div>
            )}
          />
        ) : <EmptyState title="Nenhuma empresa" description="Cadastre uma empresa cliente para liberar acesso ao CRM." />}
      </Card>
    </>
  );
}
