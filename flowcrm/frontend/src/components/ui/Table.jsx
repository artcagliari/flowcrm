export default function Table({ columns, rows, renderActions }) {
  return (
    <table className="table responsive-table">
      <thead><tr>{columns.map((c) => <th key={c.key}>{c.label}</th>)}{renderActions && <th />}</tr></thead>
      <tbody>{rows.map((row) => <tr key={row.id}>{columns.map((c) => <td key={c.key}>{c.render ? c.render(row) : row[c.key]}</td>)}{renderActions && <td>{renderActions(row)}</td>}</tr>)}</tbody>
    </table>
  );
}
