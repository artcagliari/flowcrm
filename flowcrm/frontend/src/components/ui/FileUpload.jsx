import { CloudUpload } from 'lucide-react';

export default function FileUpload() {
  return <div className="grid min-h-40 place-items-center rounded-3xl border border-dashed border-white/20 bg-white/5 text-center text-slate-400"><div><CloudUpload className="mx-auto mb-2" /><p>Arraste arquivos ou selecione para enviar</p></div></div>;
}
