import { Phone, FileText, CheckCircle2 } from 'lucide-react';

export default function Timeline() {
  const rows = [[Phone, 'Ligação registrada', 'Hoje'], [FileText, 'Documento anexado', 'Ontem'], [CheckCircle2, 'Tarefa concluída', 'Ontem']];
  return <div className="grid gap-3">{rows.map(([Icon, text, date]) => <div className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3" key={text}><span className="grid h-9 w-9 place-items-center rounded-2xl bg-blue-500/20"><Icon size={16} /></span><div><strong>{text}</strong><p className="text-sm text-slate-400">{date}</p></div></div>)}</div>;
}
