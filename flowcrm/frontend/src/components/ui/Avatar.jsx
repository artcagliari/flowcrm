export default function Avatar({ name = 'FC' }) {
  return <span className="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-blue-500 to-violet-400 text-xs font-bold text-white">{name.slice(0, 2).toUpperCase()}</span>;
}
