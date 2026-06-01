import { Download, FileText, Trash2, Upload } from 'lucide-react';
import { useEffect, useState } from 'react';
import { deleteDocument, downloadDocument, listDocuments, uploadDocument } from '../api/documents';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import FileUpload from '../components/ui/FileUpload';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
import { handleApiError } from '../utils/handleApiError';

const documentCategories = [
  { value: 'contrato', label: 'Contrato' },
  { value: 'proposta', label: 'Proposta' },
  { value: 'documento pessoal', label: 'Documento pessoal' },
  { value: 'comprovante', label: 'Comprovante' },
  { value: 'relatorio', label: 'Relatorio' },
  { value: 'recibo', label: 'Recibo' },
  { value: 'outros', label: 'Outros' },
];

function formatBytes(bytes) {
  if (!bytes) return '0 KB';
  const units = ['B', 'KB', 'MB', 'GB'];
  const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
  return `${(bytes / (1024 ** index)).toFixed(index ? 1 : 0)} ${units[index]}`;
}

export default function Documents() {
  const [documents, setDocuments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [file, setFile] = useState(null);
  const [form, setForm] = useState({ category: 'outros', client_id: '' });
  const [query, setQuery] = useState({ search: '', category: '' });
  const [message, setMessage] = useState('');

  async function load() {
    setLoading(true);
    const data = await listDocuments(query);
    setDocuments(data.data || data);
    setLoading(false);
  }

  useEffect(() => { load(); }, [JSON.stringify(query)]);

  async function submit(event) {
    event.preventDefault();
    if (!file) {
      setMessage('Selecione um arquivo para enviar.');
      return;
    }
    try {
      await uploadDocument({ ...form, file });
      setFile(null);
      setForm({ category: 'outros', client_id: '' });
      setMessage('Documento enviado. Operacao concluida.');
      await load();
    } catch (error) {
      setMessage(handleApiError(error, 'Nao foi possivel enviar o arquivo. Tente novamente.').message);
    }
  }

  async function remove(document) {
    if (!confirm('Excluir este documento?')) return;
    await deleteDocument(document.id);
    setMessage('Documento excluido com sucesso.');
    await load();
  }

  return (
    <>
      <PageHeader title="Documentos" subtitle="Guarde contratos, comprovantes, propostas e arquivos importantes." />

      {message && <div className="mb-4 rounded-2xl border border-sky-300/20 bg-sky-400/10 p-3 text-sm text-sky-100">{message}</div>}

      <div className="grid gap-4 xl:grid-cols-[.8fr_1.2fr]">
        <Card>
          <form onSubmit={submit} className="grid gap-3">
            <FileUpload file={file} onChange={setFile} />
            <p className="text-xs text-slate-400">Formatos aceitos: PDF, DOC, DOCX, XLS, XLSX, CSV, TXT, PNG, JPG, JPEG, WEBP, ZIP e RAR. Limite: 10 MB.</p>
            <Select label="Categoria" value={form.category} onChange={(event) => setForm({ ...form, category: event.target.value })}>
              {documentCategories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
            </Select>
            <Input label="ID do cliente vinculado" value={form.client_id} onChange={(event) => setForm({ ...form, client_id: event.target.value })} />
            <Button><Upload size={16} /> Enviar documento</Button>
          </form>
        </Card>

        <Card>
          <div className="mb-4 grid gap-3 md:grid-cols-2">
            <Input label="Buscar" placeholder="Buscar documento..." value={query.search} onChange={(event) => setQuery({ ...query, search: event.target.value })} />
            <Select label="Categoria" value={query.category} onChange={(event) => setQuery({ ...query, category: event.target.value })}>
              <option value="">Todas</option>
              {documentCategories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
            </Select>
          </div>
          <h2 className="mb-3 text-lg font-bold">Biblioteca</h2>
          {loading ? <EmptyState title="Carregando..." /> : documents.length ? (
            <div className="grid gap-3">
              {documents.map((document) => (
                <div key={document.id} className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3">
                  <div className="flex items-center gap-3">
                    <span className="grid h-10 w-10 place-items-center rounded-2xl bg-blue-500/20 text-sky-200"><FileText size={18} /></span>
                    <div>
                      <strong>{document.name}</strong>
                      <p className="text-sm text-slate-400">{document.category} - {formatBytes(document.size_bytes)}{document.client?.name ? ` - ${document.client.name}` : ''}</p>
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button variant="secondary" onClick={() => downloadDocument(document)}><Download size={16} /> Baixar</Button>
                    <Button variant="secondary" onClick={() => remove(document)}><Trash2 size={16} /> Excluir</Button>
                  </div>
                </div>
              ))}
            </div>
          ) : <EmptyState title="Nenhum documento" description="Envie o primeiro arquivo para montar a biblioteca." />}
        </Card>
      </div>
    </>
  );
}
