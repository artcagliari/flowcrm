import { NavLink } from 'react-router-dom';
import { Bell, CalendarDays, FolderOpen, LayoutDashboard, ListChecks, Settings, ShieldCheck, Users, Wallet } from 'lucide-react';
import Avatar from '../ui/Avatar';

const items = [
  ['/dashboard', 'Dashboard', LayoutDashboard],
  ['/clients', 'Clientes', Users],
  ['/tasks', 'Tarefas', ListChecks],
  ['/appointments', 'Agenda', CalendarDays],
  ['/finance', 'Financeiro', Wallet],
  ['/documents', 'Documentos', FolderOpen],
  ['/notifications', 'Notificacoes', Bell],
  ['/users', 'Usuarios', ShieldCheck],
  ['/settings', 'Configuracoes', Settings],
];

export default function Sidebar({ open, onClose, user }) {
  return (
    <aside className={`fixed inset-y-0 left-0 z-40 h-screen w-[286px] border-r border-white/10 bg-[#050816]/70 p-4 backdrop-blur-2xl transition lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
      <div className="glass mb-4 flex items-center gap-3 rounded-[22px] p-3">
        <div className="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-300 font-extrabold">C</div>
        <div><strong>CRM</strong><p className="text-xs text-slate-400">Gestao simples</p></div>
      </div>
      <nav className="sidebar-scroll grid max-h-[calc(100vh-180px)] gap-1 overflow-auto pr-1">
        {items.map(([to, label, Icon]) => (
          <NavLink onClick={onClose} to={to} key={to} className={({ isActive }) => `flex min-h-11 items-center gap-3 rounded-2xl border px-3 text-sm transition hover:-translate-y-0.5 ${isActive ? 'border-blue-400/40 bg-white/10 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'}`}>
            <Icon size={18} /> {label}
          </NavLink>
        ))}
      </nav>
      <div className="absolute bottom-4 left-4 right-4">
        <div className="glass flex items-center gap-3 rounded-2xl p-3">
          <Avatar name={user?.name || 'CRM'} />
          <div><strong className="block text-sm">{user?.name || 'Usuario'}</strong><span className="text-xs text-slate-400">{user?.role || 'Admin'}</span></div>
        </div>
      </div>
    </aside>
  );
}
