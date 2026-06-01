import { Link } from 'react-router-dom';
import leadsApi from '../api/leads';
import { leadStatusOptions, leadTemperatureOptions, originOptions } from '../utils/constants';
import ResourcePage, { statusColumn } from './ResourcePage';

export default function Leads() {
  return (
    <ResourcePage
      title="Leads"
      subtitle="Capture, qualifique e converta oportunidades."
      api={leadsApi}
      modalTitle="lead"
      defaults={{ status: 'novo', temperature: 'morno' }}
      fields={[
        { name: 'name', label: 'Nome' },
        { name: 'email', label: 'E-mail' },
        { name: 'phone', label: 'Telefone' },
        { name: 'origin', label: 'Origem' },
        { name: 'interest', label: 'Interesse' },
        { name: 'temperature', label: 'Temperatura', options: leadTemperatureOptions },
        { name: 'status', label: 'Status', options: leadStatusOptions },
        { name: 'estimated_value', label: 'Valor estimado', type: 'number' },
      ]}
      columns={[
        { key: 'name', label: 'Lead', render: (row) => <Link className="text-sky-300 hover:text-sky-200" to={`/leads/${row.id}`}>{row.name}</Link> },
        { key: 'phone', label: 'Telefone' },
        { key: 'origin', label: 'Origem' },
        { key: 'temperature', label: 'Temperatura' },
        statusColumn(),
      ]}
      filters={[
        { name: 'status', label: 'Status', options: leadStatusOptions },
        { name: 'temperature', label: 'Temperatura', options: leadTemperatureOptions },
        { name: 'origin', label: 'Origem', options: originOptions },
      ]}
      sortOptions={[
        { value: 'created_at', label: 'Data de cadastro' },
        { value: 'name', label: 'Nome' },
        { value: 'estimated_value', label: 'Valor estimado' },
        { value: 'temperature', label: 'Temperatura' },
        { value: 'status', label: 'Status' },
      ]}
    />
  );
}
