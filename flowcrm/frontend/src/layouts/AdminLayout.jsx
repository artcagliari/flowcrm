import { Building2, CreditCard, LayoutDashboard, LogOut, Menu, Settings } from 'lucide-react';
import { NavLink, Outlet } from 'react-router-dom';
import { useState } from 'react';
import Button from '../components/ui/Button';
import useAuth from '../hooks/useAuth';

const items = [
  ['/admin', 'Visao geral', LayoutDashboard],
  ['/admin/companies', 'Empresas', Building2],
  ['/admin/plans', 'Planos', CreditCard],
  ['/admin/settings', 'Configuracoes', Settings],
];

export default function AdminLayout() {
  const [open, setOpen] = useState(false);
  const { user, logout } = useAuth();

  return (
    <div className="min-h-screen lg:pl-[286px]">
      <aside className={`fixed inset-y-0 left-0 z-40 h-screen w-[286px] border-r border-white/10 bg-[#050816]/80 p-4 backdrop-blur-2xl transition lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="glass mb-4 rounded-[22px] p-4">
          <strong>Admin Master</strong>
          <p className="text-xs text-slate-400">{user?.email}</p>
        </div>
        <nav className="grid gap-1">
          {items.map(([to, label, Icon]) => (
            <NavLink end={to === '/admin'} onClick={() => setOpen(false)} to={to} key={to} className={({ isActive }) => `flex min-h-11 items-center gap-3 rounded-2xl border px-3 text-sm transition ${isActive ? 'border-blue-400/40 bg-white/10 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'}`}>
              <Icon size={18} /> {label}
            </NavLink>
          ))}
          <button onClick={logout} className="mt-2 flex min-h-11 items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-3 text-left text-sm text-slate-300 hover:bg-white/10">
            <LogOut size={18} /> Sair
          </button>
        </nav>
      </aside>
      <div className="min-w-0 p-3 lg:p-5">
        <div className="glass flex h-14 items-center justify-between rounded-2xl px-4 lg:hidden">
          <strong>Admin Master</strong>
          <Button variant="secondary" onClick={() => setOpen(true)}><Menu size={18} /></Button>
        </div>
        <main className="pt-6"><Outlet /></main>
      </div>
    </div>
  );
}
