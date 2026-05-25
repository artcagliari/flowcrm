import Card from '../ui/Card';

export default function StatCard({ icon: Icon, label, value, trend = '+0%' }) {
  return <Card><div className="flex items-center justify-between"><span className="grid h-10 w-10 place-items-center rounded-2xl bg-blue-500/20 text-sky-200"><Icon size={18} /></span><span className="badge badge-success">{trend}</span></div><div className="mt-6 text-3xl font-extrabold">{value}</div><p className="text-sm text-slate-400">{label}</p></Card>;
}
