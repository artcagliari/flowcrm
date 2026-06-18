import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { createDeal, listDeals, loseDeal, removeDeal, winDeal } from '../api/deals';
import { listLossReasons } from '../api/automations';
import PageHeader from '../components/shared/PageHeader';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import Input from '../components/ui/Input';
import Modal from '../components/ui/Modal';
import { formatCurrency } from '../utils/formatCurrency';

export default function Deals() {
  const [items, setItems] = useState([]);
  const [reasons, setReasons] = useState([]);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({ title: '', value: '', probability: 50, expected_close_date: '' });

  const load = () => listDeals().then((data) => setItems(data?.data || data || []));
  useEffect(() => { load(); listLossReasons().then(setReasons); }, []);

  const submit = async (e) => {
    e.preventDefault();
    await createDeal({ ...form, value: Number(form.value || 0), probability: Number(form.probability || 50) });
    setOpen(false);
    setForm({ title: '', value: '', probability: 50, expected_close_date: '' });
    load();
  };

  return (
    <>
      <PageHeader title="Oportunidades" subtitle="Deals com valor, probabilidade e previsao de fechamento." action={<Button onClick={() => setOpen(true)}>Nova oportunidade</Button>} />
      <Card className="overflow-x-auto">
        <table className="min-w-full text-sm">
          <thead><tr className="text-left text-slate-400"><th className="p-3">Titulo</th><th>Valor</th><th>Prob.</th><th>Previsao</th><th>Status</th><th /></tr></thead>
          <tbody>
            {items.map((deal) => (
              <tr key={deal.id} className="border-t border-white/10">
                <td className="p-3 font-medium">{deal.title}</td>
                <td>{formatCurrency(deal.value)}</td>
                <td>{deal.probability}%</td>
                <td>{deal.expected_close_date || '-'}</td>
                <td>{deal.status}</td>
                <td className="p-3 text-right">
                  {deal.status === 'aberto' && (
                    <div className="flex justify-end gap-2">
                      <Button size="sm" onClick={() => winDeal(deal.id).then(load)}>Ganho</Button>
                      <Button size="sm" variant="ghost" onClick={() => {
                        const reasonId = reasons[0]?.id;
                        if (reasonId) loseDeal(deal.id, { lost_reason_id: reasonId }).then(load);
                      }}>Perdido</Button>
                      <Button size="sm" variant="ghost" onClick={() => removeDeal(deal.id).then(load)}>Excluir</Button>
                    </div>
                  )}
                  {deal.lead_id && <Link className="text-sky-300" to={`/leads/${deal.lead_id}`}>Lead</Link>}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>
      <Modal open={open} onClose={() => setOpen(false)} title="Nova oportunidade">
        <form className="grid gap-3" onSubmit={submit}>
          <Input label="Titulo" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
          <Input label="Valor" type="number" value={form.value} onChange={(e) => setForm({ ...form, value: e.target.value })} />
          <Input label="Probabilidade (%)" type="number" value={form.probability} onChange={(e) => setForm({ ...form, probability: e.target.value })} />
          <Input label="Previsao de fechamento" type="date" value={form.expected_close_date} onChange={(e) => setForm({ ...form, expected_close_date: e.target.value })} />
          <Button type="submit">Salvar</Button>
        </form>
      </Modal>
    </>
  );
}
