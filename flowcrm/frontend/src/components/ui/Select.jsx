export default function Select({ label, children, options, ...props }) {
  return (
    <label className="field">
      {label ? <span>{label}</span> : null}
      <select {...props}>
        {options
          ? options.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))
          : children}
      </select>
    </label>
  );
}
