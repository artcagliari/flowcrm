<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatorio FlowCRM</title>
    <style>
        body { font-family: sans-serif; margin: 24px; color: #111; }
        h1 { font-size: 22px; }
        h2 { font-size: 16px; margin-top: 24px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f5f5f5; }
        .meta { color: #666; margin-bottom: 16px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()">Imprimir / Salvar PDF</button>
    <h1>Relatorio FlowCRM</h1>
    <p class="meta">Periodo: {{ $from }} ate {{ $to }}</p>

    <h2>Visao geral</h2>
    <table>
        <tr><th>Indicador</th><th>Valor</th></tr>
        @foreach(($data['overview'] ?? []) as $key => $value)
            <tr><td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td><td>{{ is_numeric($value) ? number_format($value, 2, ',', '.') : $value }}</td></tr>
        @endforeach
    </table>

    <h2>Leads por etapa</h2>
    <table>
        <tr><th>Etapa</th><th>Quantidade</th></tr>
        @foreach(($data['leads']['by_stage'] ?? []) as $row)
            <tr><td>{{ $row['name'] ?? '' }}</td><td>{{ $row['value'] ?? 0 }}</td></tr>
        @endforeach
    </table>

    <h2>Financeiro mensal</h2>
    <table>
        <tr><th>Mes</th><th>Receita</th></tr>
        @foreach(($data['finance']['monthly_revenue'] ?? []) as $row)
            <tr><td>{{ $row['name'] ?? '' }}</td><td>R$ {{ number_format($row['value'] ?? 0, 2, ',', '.') }}</td></tr>
        @endforeach
    </table>
</body>
</html>
