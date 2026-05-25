import { Bar, BarChart, XAxis, Tooltip } from 'recharts';
import ChartCard from '../components/dashboard/ChartCard';
import PageHeader from '../components/shared/PageHeader';

export default function Reports() {
  const data = [{ name: 'Jan', value: 12000 }, { name: 'Fev', value: 18000 }, { name: 'Mar', value: 22000 }];
  return <><PageHeader title="Relatórios" subtitle="Gráficos comerciais, financeiros e produtividade." /><div className="grid gap-4 xl:grid-cols-2"><ChartCard title="Receita mensal"><BarChart data={data}><XAxis dataKey="name" /><Tooltip /><Bar dataKey="value" fill="#7DD3FC" radius={[8,8,0,0]} /></BarChart></ChartCard><ChartCard title="Leads por origem"><BarChart data={data}><XAxis dataKey="name" /><Tooltip /><Bar dataKey="value" fill="#4F8CFF" radius={[8,8,0,0]} /></BarChart></ChartCard></div></>;
}
