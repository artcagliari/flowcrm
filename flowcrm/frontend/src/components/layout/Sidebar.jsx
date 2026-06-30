import { NavLink } from 'react-router-dom';
import { LogOut } from 'lucide-react';
import { useEffect, useState } from 'react';
import Avatar from '../ui/Avatar';
import useAuth from '../../hooks/useAuth';
import useProfessionMode from '../../hooks/useProfessionMode';
import { getWhatsappUnread } from '../../api/whatsapp';

export default function Sidebar({ open, onClose, user }) {
  const { logout } = useAuth();
  const { config } = useProfessionMode();
  const BrandIcon = config.icon;
  const [unread, setUnread] = useState(0);

  useEffect(() => {
    let active = true;
    const fetchUnread = () => getWhatsappUnread()
      .then((data) => { if (active) setUnread(data?.unread || 0); })
      .catch(() => {});
    fetchUnread();
    const timer = setInterval(fetchUnread, 30000);
    return () => { active = false; clearInterval(timer); };
  }, []);

  return (
    <aside className={`fixed inset-y-0 left-0 z-40 h-screen w-[286px] border-r border-white/10 bg-[#050816]/70 p-4 backdrop-blur-2xl transition lg:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
      <div className="glass mb-4 flex items-center gap-3 rounded-[22px] p-3">
        <div className={`grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br ${config.accent} font-extrabold text-white`}>
          <BrandIcon size={20} />
        </div>
        <div>
          <strong>FlowCRM</strong>
          <p className="text-xs text-slate-400">{config.workspace} · {config.label}</p>
        </div>
      </div>
      <nav className="sidebar-scroll grid max-h-[calc(100vh-180px)] gap-1 overflow-auto pr-1">
        {config.nav.map(([to, label, Icon]) => (
          <NavLink
            onClick={onClose}
            to={to}
            key={to}
            className={({ isActive }) => `flex min-h-11 items-center gap-3 rounded-2xl border px-3 text-sm transition hover:-translate-y-0.5 ${isActive ? 'border-[color:var(--primary)] bg-white/10 text-white' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10'}`}
          >
            <Icon size={18} /> <span className="flex-1">{label}</span>
            {to === '/whatsapp' && unread > 0 && (
              <span className="grid h-5 min-w-5 place-items-center rounded-full bg-sky-500 px-1 text-xs text-white">{unread}</span>
            )}
          </NavLink>
        ))}
      </nav>
      <div className="absolute bottom-4 left-4 right-4">
        <div className="glass flex items-center gap-3 rounded-2xl p-3">
          <Avatar name={user?.name || 'CRM'} />
          <div className="min-w-0 flex-1">
            <strong className="block truncate text-sm">{user?.name || 'Usuario'}</strong>
            <span className="text-xs text-slate-400">{config.label}</span>
          </div>
          <button onClick={logout} title="Sair" className="grid h-9 w-9 place-items-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white">
            <LogOut size={16} />
          </button>
        </div>
      </div>
    </aside>
  );
}
