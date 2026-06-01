import { Edit3, Plus, Save, ShieldCheck, Trash2, UserCog, UserRound } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { createUser, deleteUser, listUsers, updateProfile, updateUser } from '../api/users';
import PageHeader from '../components/shared/PageHeader';
import Avatar from '../components/ui/Avatar';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
import useAuth from '../hooks/useAuth';

const roleLabels = {
  owner: 'Owner',
  admin: 'Admin',
  employee: 'Colaborador',
  financial: 'Financeiro',
  viewer: 'Visualizador',
  dono: 'Dono',
  admin_company: 'Admin company',
  agente: 'Agente',
  superadmin: 'Superadmin',
};

const statusOptions = [
  { value: 'ativo', label: 'Ativo' },
  { value: 'inativo', label: 'Inativo' },
];

const blankUser = { name: '', email: '', password: '', status: 'ativo', role: 'employee' };

export default function Users() {
  const { user, refreshUser } = useAuth();
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState(['dono', 'admin_company', 'agente']);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState(blankUser);
  const [profile, setProfile] = useState({ name: user?.name || '', email: user?.email || '', password: '' });
  const [message, setMessage] = useState('');

  async function load() {
    setLoading(true);
    const data = await listUsers();
    setUsers(data.users || []);
    setRoles(data.roles || roles);
    setCanManage(Boolean(data.can_manage_users));
    setLoading(false);
  }

  useEffect(() => { load(); }, []);
  useEffect(() => setProfile({ name: user?.name || '', email: user?.email || '', password: '' }), [user]);

  const orderedUsers = useMemo(() => [...users].sort((a, b) => {
    const roleOrder = ['superadmin', 'owner', 'admin', 'financial', 'employee', 'viewer', 'dono', 'admin_company', 'agente'];
    return roleOrder.indexOf(a.role) - roleOrder.indexOf(b.role) || a.name.localeCompare(b.name);
  }), [users]);

  function startCreate() {
    setEditingId(null);
    setForm({ ...blankUser, role: roles.includes('employee') ? 'employee' : roles[0] });
  }

  function startEdit(member) {
    setEditingId(member.id);
    setForm({ name: member.name, email: member.email, password: '', status: member.status || 'ativo', role: member.role || 'employee' });
  }

  async function saveUser(event) {
    event.preventDefault();
    if (!canManage) return;

    if (editingId) {
      await updateUser(editingId, form);
      setMessage('Usuario atualizado com sucesso.');
    } else {
      await createUser(form);
      setMessage('Usuario criado com sucesso.');
    }

    setForm(blankUser);
    setEditingId(null);
    await load();
  }

  async function removeUser(member) {
    if (!confirm(`Remover ${member.name} da empresa?`)) return;
    await deleteUser(member.id);
    setMessage('Usuario removido com sucesso.');
    await load();
  }

  async function saveProfile(event) {
    event.preventDefault();
    const updated = await updateProfile(profile);
    refreshUser(updated);
    setProfile({ name: updated.name, email: updated.email, password: '' });
    setMessage('Perfil atualizado com sucesso.');
    await load();
  }

  return (
    <>
      <PageHeader title="Usuarios" subtitle="Gerencie quem pode acessar o CRM e suas permissoes." />

      {message && <div className="mb-4 rounded-2xl border border-sky-300/20 bg-sky-400/10 p-3 text-sm text-sky-100">{message}</div>}

      <div className="grid gap-4 xl:grid-cols-[.85fr_1.15fr]">
        <div className="grid gap-4">
          <Card>
            <div className="mb-4 flex items-center gap-3">
              <Avatar name={user?.name || 'Usuario'} />
              <div>
                <h2 className="text-lg font-bold">Meu perfil</h2>
                <p className="text-sm text-slate-400">Atualize seus dados de acesso.</p>
              </div>
            </div>
            <form onSubmit={saveProfile} className="grid gap-3">
              <Input label="Nome" value={profile.name} onChange={(event) => setProfile({ ...profile, name: event.target.value })} required />
              <Input label="E-mail" type="email" value={profile.email} onChange={(event) => setProfile({ ...profile, email: event.target.value })} required />
              <Input label="Nova senha" type="password" value={profile.password} onChange={(event) => setProfile({ ...profile, password: event.target.value })} placeholder="Deixe vazio para manter" />
              <Button><Save size={16} /> Salvar perfil</Button>
            </form>
          </Card>

          <Card>
            <div className="mb-4 flex items-center justify-between gap-3">
              <div>
                <h2 className="text-lg font-bold">{editingId ? 'Editar usuario' : 'Novo usuario'}</h2>
                <p className="text-sm text-slate-400">Owner, admin e superadmin gerenciam equipe.</p>
              </div>
              {canManage && <Button variant="secondary" onClick={startCreate}><Plus size={16} /> Novo</Button>}
            </div>
            {canManage ? (
              <form onSubmit={saveUser} className="grid gap-3">
                <Input label="Nome" value={form.name} onChange={(event) => setForm({ ...form, name: event.target.value })} required />
                <Input label="E-mail" type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} required />
                <Input label={editingId ? 'Nova senha' : 'Senha'} type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} required={!editingId} placeholder={editingId ? 'Deixe vazio para manter' : ''} />
                <div className="grid gap-3 md:grid-cols-2">
                  <Select label="Status" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}>
                    {statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                  </Select>
                  <Select label="Cargo" value={form.role} onChange={(event) => setForm({ ...form, role: event.target.value })}>
                    {roles.map((role) => <option key={role} value={role}>{roleLabels[role] || role}</option>)}
                  </Select>
                </div>
                <Button><Save size={16} /> {editingId ? 'Salvar alteracoes' : 'Criar usuario'}</Button>
              </form>
            ) : (
              <EmptyState title="Sem permissao para gerenciar equipe" description="Voce ainda pode editar o proprio perfil acima." />
            )}
          </Card>
        </div>

        <Card>
          <div className="mb-4 flex items-center justify-between gap-3">
            <div>
              <h2 className="text-lg font-bold">Equipe da empresa</h2>
              <p className="text-sm text-slate-400">Cargos disponiveis: owner, admin, colaborador, financeiro e visualizador.</p>
            </div>
            <span className="grid h-11 w-11 place-items-center rounded-2xl bg-blue-500/20 text-sky-100"><UserCog size={18} /></span>
          </div>

          {loading ? <EmptyState title="Carregando usuarios..." /> : (
            <div className="grid gap-3">
              {orderedUsers.map((member) => (
                <div key={member.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3">
                  <div className="flex min-w-0 items-center gap-3">
                    <Avatar name={member.name} />
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <strong className="truncate">{member.name}</strong>
                        {member.is_superadmin && <ShieldCheck className="text-sky-300" size={16} />}
                      </div>
                      <p className="truncate text-sm text-slate-400">{member.email}</p>
                    </div>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Badge>{roleLabels[member.role] || member.role || 'sem cargo'}</Badge>
                    <Badge>{member.status}</Badge>
                    {canManage && (
                      <Button variant="secondary" onClick={() => startEdit(member)}><Edit3 size={16} /> Editar</Button>
                    )}
                    {canManage && member.id !== user?.id && (
                      <Button variant="secondary" onClick={() => removeUser(member)}><Trash2 size={16} /> Remover</Button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </Card>
      </div>
    </>
  );
}
