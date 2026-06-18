import { useEffect, useState } from 'react';
import { createAutomation, createSequence, createTemplate, listAutomations, listSequences, listTemplates, removeAutomation } from '../api/automations';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Select from '../components/ui/Select';
import Textarea from '../components/ui/Textarea';

export default function Automations() {
  const [automations, setAutomations] = useState([]);
  const [templates, setTemplates] = useState([]);
  const [sequences, setSequences] = useState([]);
  const [form, setForm] = useState({ name: '', trigger_type: 'lead.created', action_type: 'create_task', action_config: { title: 'Follow-up automatico', due_days: 1 } });

  const load = () => Promise.all([
    listAutomations().then(setAutomations),
    listTemplates().then(setTemplates),
    listSequences().then(setSequences),
  ]);
  useEffect(() => { load(); }, []);

  return (
    <>
      <PageHeader title="Automacoes" subtitle="Regras IF/THEN, templates e sequencias de follow-up." />
      <section className="grid gap-4 xl:grid-cols-2">
        <Card>
          <h3 className="mb-3 font-semibold">Nova automacao</h3>
          <form className="grid gap-3" onSubmit={async (e) => { e.preventDefault(); await createAutomation(form); load(); }}>
            <Input label="Nome" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
            <Select label="Gatilho" value={form.trigger_type} onChange={(e) => setForm({ ...form, trigger_type: e.target.value })} options={[
              { value: 'lead.created', label: 'Lead criado' },
              { value: 'lead.stage_changed', label: 'Etapa alterada' },
              { value: 'lead.lost', label: 'Lead perdido' },
              { value: 'deal.won', label: 'Deal ganho' },
            ]} />
            <Select label="Acao" value={form.action_type} onChange={(e) => setForm({ ...form, action_type: e.target.value })} options={[
              { value: 'create_task', label: 'Criar tarefa' },
              { value: 'notify_user', label: 'Notificar usuario' },
              { value: 'send_email', label: 'Enviar e-mail' },
              { value: 'send_whatsapp', label: 'Enviar WhatsApp' },
            ]} />
            <Button type="submit">Salvar automacao</Button>
          </form>
        </Card>
        <Card>
          <h3 className="mb-3 font-semibold">Automacoes ativas</h3>
          <div className="grid gap-2">
            {automations.map((item) => (
              <div key={item.id} className="rounded-2xl border border-white/10 p-3">
                <strong>{item.name}</strong>
                <p className="text-sm text-slate-400">{item.trigger_type} → {item.action_type}</p>
                <Button size="sm" variant="ghost" className="mt-2" onClick={() => removeAutomation(item.id).then(load)}>Excluir</Button>
              </div>
            ))}
          </div>
        </Card>
      </section>
      <section className="mt-4 grid gap-4 xl:grid-cols-2">
        <Card>
          <h3 className="mb-3 font-semibold">Templates</h3>
          {templates.map((t) => <p key={t.id} className="text-sm">{t.name} ({t.channel})</p>)}
          <form className="mt-3 grid gap-2" onSubmit={async (e) => {
            e.preventDefault();
            const fd = new FormData(e.currentTarget);
            await createTemplate({ name: fd.get('name'), channel: fd.get('channel'), body: fd.get('body') });
            load();
            e.currentTarget.reset();
          }}>
            <Input name="name" label="Nome" required />
            <Select name="channel" label="Canal" options={[{ value: 'whatsapp', label: 'WhatsApp' }, { value: 'email', label: 'E-mail' }]} />
            <Textarea name="body" label="Corpo" required />
            <Button type="submit">Criar template</Button>
          </form>
        </Card>
        <Card>
          <h3 className="mb-3 font-semibold">Sequencias</h3>
          {sequences.map((s) => <p key={s.id} className="text-sm">{s.name} ({s.steps?.length || 0} passos)</p>)}
          <form className="mt-3 grid gap-2" onSubmit={async (e) => {
            e.preventDefault();
            const fd = new FormData(e.currentTarget);
            await createSequence({
              name: fd.get('name'),
              trigger_type: 'lead_created',
              steps: [{ delay_days: 1, action_type: 'create_task', action_config: { title: 'Follow-up D+1' } }],
            });
            load();
            e.currentTarget.reset();
          }}>
            <Input name="name" label="Nome da sequencia" required />
            <Button type="submit">Criar sequencia basica</Button>
          </form>
        </Card>
      </section>
    </>
  );
}
