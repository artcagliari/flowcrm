import { ResponsiveContainer } from 'recharts';
import Card from '../ui/Card';

export default function ChartCard({ title, children }) {
  return <Card className="min-h-80"><h2 className="mb-4 text-lg font-bold">{title}</h2><ResponsiveContainer width="100%" height={230}>{children}</ResponsiveContainer></Card>;
}
