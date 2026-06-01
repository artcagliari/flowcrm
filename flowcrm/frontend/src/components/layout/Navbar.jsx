import { Bell, Menu, Plus, Search } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import Button from '../ui/Button';

export default function Navbar({ onMenu }) {
  const navigate = useNavigate();
  return (
    <header className="glass sticky top-4 z-30 flex min-h-16 items-center gap-3 rounded-[24px] p-3">
      <button className="rounded-2xl border border-white/10 bg-white/5 p-3 lg:hidden" onClick={onMenu}><Menu size={18} /></button>
      <div className="flex h-11 flex-1 items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 text-slate-400">
        <Search size={17} /><input className="w-full bg-transparent text-slate-100 outline-none" placeholder="Buscar clientes, tarefas, compromissos..." />
      </div>
      <button onClick={() => navigate('/notifications')} className="relative rounded-2xl border border-white/10 bg-white/5 p-3"><Bell size={18} /><span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500" /></button>
      <Button><Plus size={16} /> Novo</Button>
    </header>
  );
}
