<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Reservas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background: #3B82F6;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 12px;
        }
        
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #3B82F6;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background: #3B82F6;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-pendente { background: #FEF3C7; color: #92400E; }
        .badge-aprovada { background: #D1FAE5; color: #065F46; }
        .badge-rejeitada { background: #FEE2E2; color: #991B1B; }
        .badge-cancelada { background: #E5E7EB; color: #374151; }
        .badge-concluida { background: #DBEAFE; color: #1E40AF; }
        
        .footer {
            text-align: center;
            font-size: 10px;
            color: #6B7280;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Relatório de Reservas de Espaços Físicos</h1>
        <p>Gerado em: {{ $data_geracao }}</p>
    </div>

    <div class="info-box">
        <h3>Filtros Aplicados</h3>
        @if(isset($filtros['data_inicio']) || isset($filtros['data_fim']))
            <div class="info-row">
                <strong>Período:</strong>
                <span>
                    {{ isset($filtros['data_inicio']) ? \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') : '-' }}
                    até
                    {{ isset($filtros['data_fim']) ? \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') : '-' }}
                </span>
            </div>
        @endif
        @if(isset($filtros['status']))
            <div class="info-row">
                <strong>Status:</strong>
                <span>{{ $filtros['status'] }}</span>
            </div>
        @endif
        <div class="info-row">
            <strong>Total de Reservas:</strong>
            <span>{{ $reservas->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Motivo</th>
                <th>Espaço</th>
                <th>Data</th>
                <th>Horário</th>
                <th>Solicitante</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
                <tr>
                    <td>#{{ $reserva->id }}</td>
                    <td>{{ $reserva->motivo }}</td>
                    <td>{{ $reserva->espacoFisico->nome ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') }}</td>
                    <td>{{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}</td>
                    <td>{{ $reserva->solicitante->name ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($reserva->status) }}">
                            {{ $reserva->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Nexus ACAD - Sistema de Gestão Acadêmica</p>
        <p>Este relatório foi gerado automaticamente pelo sistema</p>
    </div>
</body>
</html>
