export default function EmptyState({ title = 'Nada por aqui', description = 'Crie um novo registro para começar.' }) {
  return <div className="grid min-h-48 place-items-center text-center text-slate-400"><div><h3 className="font-semibold text-slate-100">{title}</h3><p>{description}</p></div></div>;
}
