export default function Input({ label, as = 'input', ...props }) {
  const Component = as;
  return <label className="field"><span>{label}</span><Component {...props} /></label>;
}
