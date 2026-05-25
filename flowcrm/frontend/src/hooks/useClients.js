import clientsApi from '../api/clients';
import { useApiResource } from './useApiResource';
export const useClients = () => useApiResource(clientsApi);
