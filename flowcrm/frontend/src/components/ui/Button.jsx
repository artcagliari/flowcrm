import { clsx } from 'clsx';

export default function Button({ variant = 'primary', className, ...props }) {
  return <button className={clsx('inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl border px-4 text-sm font-semibold transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60', variant === 'primary' ? 'border-[color:var(--primary)] bg-[color:var(--primary)] text-white shadow-lg shadow-blue-500/20' : 'border-white/10 bg-white/5 text-slate-100 hover:bg-white/10', className)} {...props} />;
}
