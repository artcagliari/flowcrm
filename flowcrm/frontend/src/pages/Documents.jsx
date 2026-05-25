import Card from '../components/ui/Card';
import FileUpload from '../components/ui/FileUpload';
import PageHeader from '../components/shared/PageHeader';

export default function Documents() {
  return <><PageHeader title="Documentos" subtitle="Upload, categorias e arquivos vinculados." /><div className="grid gap-4 xl:grid-cols-[.8fr_1.2fr]"><FileUpload /><Card><h2 className="text-lg font-bold">Biblioteca</h2><p className="mt-2 text-slate-400">Endpoint de documentos já está preparado para upload e download.</p></Card></div></>;
}
