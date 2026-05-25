import { motion } from 'framer-motion';
import { X } from 'lucide-react';

export default function Modal({ title, open, onClose, children }) {
  if (!open) return null;
  return (
    <div className="fixed inset-0 z-50 grid place-items-center bg-black/45 p-4 backdrop-blur-md">
      <motion.div initial={{ opacity: 0, y: 18, scale: .98 }} animate={{ opacity: 1, y: 0, scale: 1 }} className="glass max-h-[88vh] w-full max-w-2xl overflow-auto rounded-[28px] p-5">
        <div className="mb-4 flex items-start justify-between gap-4">
          <div><span className="text-xs font-bold uppercase text-sky-300">FlowCRM</span><h2 className="text-xl font-bold">{title}</h2></div>
          <button className="rounded-2xl border border-white/10 bg-white/5 p-2" onClick={onClose}><X size={18} /></button>
        </div>
        {children}
      </motion.div>
    </div>
  );
}
