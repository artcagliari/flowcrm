export default function Textarea({ label, ...props }) {
  return <label className="field"><span>{label}</span><textarea {...props} /></label>;
}
