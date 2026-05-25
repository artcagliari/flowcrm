import leadsApi from '../api/leads';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Leads() {
  return <ResourcePage title="Leads" subtitle="Capture, qualifique e converta oportunidades." api={leadsApi} modalTitle="lead" defaults={{ status: 'novo', temperature: 'morno' }} fields={[{ name: 'name', label: 'Nome' }, { name: 'email', label: 'E-mail' }, { name: 'phone', label: 'Telefone' }, { name: 'origin', label: 'Origem' }, { name: 'interest', label: 'Interesse' }, { name: 'temperature', label: 'Temperatura' }, { name: 'estimated_value', label: 'Valor estimado', type: 'number' }]} columns={[{ key: 'name', label: 'Lead' }, { key: 'phone', label: 'Telefone' }, { key: 'origin', label: 'Origem' }, { key: 'temperature', label: 'Temperatura' }, statusColumn()]} />;
}
