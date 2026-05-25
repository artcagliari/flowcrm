import { Plus } from 'lucide-react';
import { useState } from 'react';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import EmptyState from '../components/ui/EmptyState';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import Table from '../components/ui/Table';
import PageHeader from '../components/shared/PageHeader';
import { useApiResource } from '../hooks/useApiResource';

export default function ResourcePage({ title, subtitle, api, fields, columns, defaults = {}, modalTitle }) {
  const { items, loading, reload } = useApiResource(api);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(defaults);
  const [editing, setEditing] = useState(null);

  async function submit(e) {
    e.preventDefault();
    if (editing) await api.update(editing.id, form);
    else await api.create(form);
    setOpen(false);
    setEditing(null);
    setForm(defaults);
    reload();
  }

  function edit(row) {
    setEditing(row);
    setForm(row);
    setOpen(true);
  }

  async function remove(row) {
    if (confirm('Excluir este registro?')) {
      await api.remove(row.id);
      reload();
    }
  }

  return <><PageHeader title={title} subtitle={subtitle}><Button onClick={() => setOpen(true)}><Plus size={16} /> Novo</Button></PageHeader><Card>{loading ? <EmptyState title="Carregando..." /> : items.length ? <Table columns={columns} rows={items} renderActions={(row) => <div className="flex gap-2"><Button variant="secondary" onClick={() => edit(row)}>Editar</Button><Button variant="secondary" onClick={() => remove(row)}>Excluir</Button></div>} /> : <EmptyState />}</Card><Modal title={editing ? `Editar ${modalTitle}` : `Novo ${modalTitle}`} open={open} onClose={() => setOpen(false)}><form onSubmit={submit} className="grid gap-3">{fields.map((field) => <Input key={field.name} label={field.label} type={field.type || 'text'} value={form[field.name] || ''} onChange={(e) => setForm({ ...form, [field.name]: e.target.value })} />)}<Button>Salvar</Button></form></Modal></>;
}

export function statusColumn(key = 'status') {
  return { key, label: 'Status', render: (row) => <Badge>{row[key]}</Badge> };
}
