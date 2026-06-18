import useAuth from './useAuth';
import { getProfessionConfig } from '../config/profession';

export default function useProfessionMode() {
  useAuth();
  const mode = 'empresa';
  const config = getProfessionConfig();

  return { mode, config };
}
