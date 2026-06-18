import { MessageCircle } from 'lucide-react';
import Button from '../ui/Button';

function digits(phone, whatsapp) {
  return (whatsapp || phone || '').replace(/\D/g, '');
}

export default function WhatsappActionButton({
  phone,
  whatsapp,
  compact = false,
  label = 'Mensagem',
}) {
  const number = digits(phone, whatsapp);

  function openChat(event) {
    event?.preventDefault?.();
    event?.stopPropagation?.();
    if (!number) return;
    window.open(`https://wa.me/${number}`, '_blank', 'noopener,noreferrer');
  }

  if (compact) {
    return (
      <button
        type="button"
        onClick={openChat}
        disabled={!number}
        title={number ? 'Enviar mensagem no WhatsApp' : 'Cadastre telefone ou WhatsApp'}
        className={`grid h-8 w-8 place-items-center rounded-xl border transition ${
          number
            ? 'border-green-400/30 bg-green-500/10 text-green-200 hover:bg-green-500/20'
            : 'cursor-not-allowed border-white/5 bg-white/5 text-slate-600'
        }`}
      >
        <MessageCircle size={15} />
      </button>
    );
  }

  return (
    <Button variant="secondary" onClick={openChat} disabled={!number} title={number ? undefined : 'Cadastre telefone ou WhatsApp'}>
      <MessageCircle size={16} /> {label}
    </Button>
  );
}
