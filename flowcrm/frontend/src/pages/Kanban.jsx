import { DndContext, useDraggable, useDroppable } from '@dnd-kit/core';
import { CSS } from '@dnd-kit/utilities';
import { useEffect, useState } from 'react';
import { getKanban, moveLead } from '../api/kanban';
import PageHeader from '../components/shared/PageHeader';
import Badge from '../components/ui/Badge';
import Card from '../components/ui/Card';
import { formatCurrency } from '../utils/formatCurrency';

export default function Kanban() {
  const [columns, setColumns] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => { getKanban().then(setColumns); }, []);

  async function onDragEnd(event) {
    const leadId = event.active?.id;
    const stageId = event.over?.id;
    if (!leadId || !stageId) return;

    const previous = columns;
    const movedLead = previous.flatMap((column) => column.leads).find((lead) => lead.id === leadId);
    if (!movedLead) return;

    setColumns((current) => current
      .map((column) => ({ ...column, leads: column.leads.filter((lead) => lead.id !== leadId) }))
      .map((column) => (column.id === stageId ? { ...column, leads: [...column.leads, movedLead] } : column)));

    try {
      await moveLead(leadId, stageId);
    } catch {
      setColumns(previous);
      setError('Nao foi possivel mover o lead.');
    }
  }

  return (
    <>
      <PageHeader title="Funil Kanban" subtitle="Arraste oportunidades entre etapas do pipeline comercial." />
      {error && <p className="mb-3 rounded-2xl border border-red-400/20 bg-red-500/10 p-3 text-red-200">{error}</p>}
      <DndContext onDragEnd={onDragEnd}>
        <section className="grid auto-cols-[300px] grid-flow-col gap-4 overflow-x-auto pb-4">
          {columns.map((column) => <KanbanColumn key={column.id} column={column} />)}
        </section>
      </DndContext>
    </>
  );
}

function KanbanColumn({ column }) {
  const { setNodeRef } = useDroppable({ id: column.id });
  return (
    <div ref={setNodeRef} className="glass min-h-[560px] rounded-[24px] p-4">
      <div className="mb-3 flex items-center justify-between">
        <h2 className="font-bold">{column.name}</h2>
        <Badge>{column.leads.length}</Badge>
      </div>
      <div className="grid gap-3">{column.leads.map((lead) => <KanbanCard key={lead.id} lead={lead} />)}</div>
    </div>
  );
}

function KanbanCard({ lead }) {
  const { attributes, listeners, setNodeRef, transform } = useDraggable({ id: lead.id });
  return (
    <div ref={setNodeRef} style={{ transform: CSS.Translate.toString(transform) }} {...listeners} {...attributes}>
      <Card className="cursor-grab">
        <strong>{lead.name}</strong>
        <p className="text-sm text-slate-400">{lead.phone || lead.email}</p>
        <div className="mt-3 flex justify-between">
          <Badge>{lead.temperature}</Badge>
          <span className="text-sm text-slate-400">{formatCurrency(lead.estimated_value || 0)}</span>
        </div>
      </Card>
    </div>
  );
}
