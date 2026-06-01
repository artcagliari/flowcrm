import { Plus } from 'lucide-react';
import { useState } from 'react';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import Select from '../components/ui/Select';
import Table from '../components/ui/Table';
import PageHeader from '../components/shared/PageHeader';
import { useApiResource } from '../hooks/useApiResource';

export default function ResourcePage({ title, subtitle, api, fields, columns, defaults = {}, modalTitle, filters = [], sortOptions = [], transformSubmit, prepareEdit }) {
  const [query, setQuery] = useState({
    search: '',
    sort_by: sortOptions[0]?.value || 'created_at',
    sort_dir: 'desc',
  });
  const { items, loading, reload } = useApiResource(api, query);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(defaults);
  const [editing, setEditing] = useState(null);

  async function submit(e) {
    e.preventDefault();
    const payload = transformSubmit ? transformSubmit(form) : form;
    if (editing) await api.update(editing.id, payload);
    else await api.create(payload);
    setOpen(false);
    setEditing(null);
    setForm(defaults);
    reload();
  }

  function edit(row) {
    setEditing(row);
    setForm(prepareEdit ? prepareEdit(row) : row);
    setOpen(true);
  }

  async function remove(row) {
    if (confirm('Excluir este registro?')) {
      await api.remove(row.id);
      reload();
    }
  }

  return <><PageHeader title={title} subtitle={subtitle}><Button onClick={() => setOpen(true)}><Plus size={16} /> Novo</Button></PageHeader><Card className="mb-4"><div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4"><Input label="Buscar" value={query.search} placeholder={`Buscar ${modalTitle}...`} onChange={(e) => setQuery({ ...query, search: e.target.value })} />{filters.map((filter) => <Select key={filter.name} label={filter.label} value={query[filter.name] || ''} onChange={(e) => setQuery({ ...query, [filter.name]: e.target.value })}><option value="">Todos</option>{filter.options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select>)}<Select label="Ordenar por" value={query.sort_by} onChange={(e) => setQuery({ ...query, sort_by: e.target.value })}>{sortOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select><Select label="Direção" value={query.sort_dir} onChange={(e) => setQuery({ ...query, sort_dir: e.target.value })}><option value="desc">Mais recentes primeiro</option><option value="asc">Mais antigos primeiro</option></Select></div></Card><Card>{loading ? <EmptyState title="Carregando..." /> : items.length ? <Table columns={columns} rows={items} renderActions={(row) => <div className="flex gap-2"><Button variant="secondary" onClick={() => edit(row)}>Editar</Button><Button variant="secondary" onClick={() => remove(row)}>Excluir</Button></div>} /> : <EmptyState title="Nenhum resultado" description="Ajuste a busca ou os filtros para ver mais registros." />}</Card><Modal title={editing ? `Editar ${modalTitle}` : `Novo ${modalTitle}`} open={open} onClose={() => setOpen(false)}><form onSubmit={submit} className="grid gap-3">{fields.map((field) => field.options ? <Select key={field.name} label={field.label} value={form[field.name] || ''} onChange={(e) => setForm({ ...form, [field.name]: e.target.value })}><option value="">Selecione</option>{field.options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</Select> : <Input key={field.name} label={field.label} type={field.type || 'text'} value={form[field.name] || ''} onChange={(e) => setForm({ ...form, [field.name]: e.target.value })} />)}<Button>Salvar</Button></form></Modal></>;
}

export function statusColumn(key = 'status') {
  return { key, label: 'Status', render: (row) => <Badge>{row[key]}</Badge> };
}
