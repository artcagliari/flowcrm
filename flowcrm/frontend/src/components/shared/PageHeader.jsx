export default function PageHeader({ title, subtitle, children }) {
  return <div className="mb-5 flex flex-wrap items-start justify-between gap-4"><div><span className="text-xs font-bold uppercase text-sky-300">CRM</span><h1 className="mt-1 text-3xl font-extrabold tracking-tight lg:text-4xl">{title}</h1><p className="mt-2 max-w-2xl text-sm text-slate-400">{subtitle}</p></div><div className="flex flex-wrap gap-2">{children}</div></div>;
}
