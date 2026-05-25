import { CloudUpload } from 'lucide-react';

export default function FileUpload({ file, onChange }) {
  return (
    <label className="grid min-h-40 cursor-pointer place-items-center rounded-3xl border border-dashed border-white/20 bg-white/5 text-center text-slate-400 transition hover:border-sky-300/50 hover:bg-white/10">
      <input className="hidden" type="file" onChange={(event) => onChange?.(event.target.files?.[0] || null)} />
      <div>
        <CloudUpload className="mx-auto mb-2" />
        <p>{file ? file.name : 'Clique para selecionar um arquivo'}</p>
        <span className="mt-1 block text-xs text-slate-500">PDF, imagem, planilha ou documento ate 10MB</span>
      </div>
    </label>
  );
}
