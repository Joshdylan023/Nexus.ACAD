<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Logs de Auditoria</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            background-color: #f9f9f9;
        }
        .filters p {
            margin: 0 0 5px 0;
            font-size: 11px;
        }
        .filters strong {
            display: inline-block;
            min-width: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #777;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
        .details {
            max-width: 350px;
            word-wrap: break-word;
        }
        .user-info {
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Logs de Auditoria</h1>
        <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if($filters)
        <div class="filters">
            <p><strong>Filtros Aplicados:</strong></p>
            @foreach($filters as $key => $value)
                @if($value)
                    <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
                @endif
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Módulo</th>
                <th>Detalhes</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td class="user-info">
                        @if($log->user)
                            {{ $log->user->name }}<br>
                            ({{ $log->user->email }})
                        @else
                            Sistema
                        @endif
                    </td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->module ?? 'N/A' }}</td>
                    <td class="details">
                        <strong>Entidade:</strong> {{ $log->auditable_type }} (ID: {{ $log->auditable_id }})<br>
                        <strong>Descrição:</strong> {{ $log->description ?? '---' }}
                    </td>
                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum log encontrado para os filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p class="page-number"></p>
    </div>
</body>
</html>
