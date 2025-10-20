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
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        
        body {
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background: {{ $identidadeVisual->cor_primaria ?? '#3B82F6' }};
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 11px;
        }

        .info-box {
            background: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid {{ $identidadeVisual->cor_secundaria ?? '#3B82F6' }};
        }
        
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: {{ $identidadeVisual->cor_primaria ?? '#3B82F6' }};
        }
        
        .info-row {
            margin-bottom: 8px;
            font-size: 11px;
        }
        .info-row span {
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background: {{ $identidadeVisual->cor_primaria ?? '#3B82F6' }};
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            vertical-align: middle;
        }
        
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-pendente { background: #FEF3C7; color: #92400E; }
        .badge-aprovada { background: #D1FAE5; color: #065F46; }
        .badge-rejeitada { background: #FEE2E2; color: #991B1B; }
        .badge-cancelada { background: #E5E7EB; color: #374151; }
        .badge-concluída { background: #DBEAFE; color: #1E40AF; }
        
        .footer {
            text-align: center;
            font-size: 9px;
            color: #6B7280;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .page-number:after {
            content: counter(page);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    @if($logoBase64)
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 60px;">
        </div>
    @endif

    <div class="header">
        <h1>Relatório de Reservas de Espaços</h1>
        <p>{{ $instituicao->nome_fantasia ?? $instituicao->razao_social ?? '' }}</p>
    </div>

    <div class="info-box">
        <h3>Filtros e Informações</h3>
        <div class="info-row">
            Gerado em: <span>{{ $dataGeracao }}</span> por <span>{{ $usuario }}</span>
        </div>
        @if(array_filter((array)$filtros))
            @if($filtros['periodo'])
                <div class="info-row">
                    Período: <span>{{ $filtros['periodo'] }}</span>
                </div>
            @endif
            @if($filtros['status'])
                <div class="info-row">
                    Status: <span>{{ $filtros['status'] }}</span>
                </div>
            @endif
            @if($filtros['espaco'])
                <div class="info-row">
                    Espaço: <span>{{ $filtros['espaco'] }}</span>
                </div>
            @endif
        @endif
        <div class="info-row">
            Total de Reservas: <span>{{ $reservas->count() }}</span>
        </div>
    </div>

    @if($reservas->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Espaço</th>
                <th>Solicitante</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Horário</th>
                <th>Finalidade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
                <tr>
                    <td>{{ $reserva->id }}</td>
                    <td>{{ $reserva->espacoFisico->nome ?? '-' }}</td>
                    <td>{{ $reserva->solicitante->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y') }}</td>
                    <td>{{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}</td>
                    <td>{{ $reserva->finalidade ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($reserva->status) }}">
                            {{ $reserva->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <p>Nenhuma reserva encontrada com os filtros aplicados.</p>
    </div>
    @endif

    <div class="footer">
        Página <span class="page-number"></span> | Nexus ACAD - Sistema de Gestão Acadêmica
    </div>
</body>
</html>