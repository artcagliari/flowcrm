import { CheckCircle2 } from 'lucide-react';

export default function Toast({ message }) {
  if (!message) return null;
  return <div className="glass fixed bottom-5 right-5 z-50 inline-flex items-center gap-2 rounded-2xl px-4 py-3"><CheckCircle2 size={18} />{message}</div>;
}
