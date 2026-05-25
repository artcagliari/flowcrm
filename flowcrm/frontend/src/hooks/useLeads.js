import leadsApi from '../api/leads';
import { useApiResource } from './useApiResource';
export const useLeads = () => useApiResource(leadsApi);
