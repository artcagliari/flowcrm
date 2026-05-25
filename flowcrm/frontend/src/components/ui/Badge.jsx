import { STATUS_BADGE } from '../../utils/constants';

export default function Badge({ children, tone }) {
  const badgeTone = tone || STATUS_BADGE[String(children).toLowerCase()] || 'primary';
  return <span className={`badge badge-${badgeTone}`}>{children}</span>;
}
