<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Logs de Auditoria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .header-info {
            margin-bottom: 15px;
            font-size: 10px;
            line-height: 1.6;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
    </style>
</head>
<body>
    <h1>Relatório de Logs de Auditoria - Nexus.ACAD</h1>
    
    <div class="header-info">
        <strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
        @if(!empty($filters['module']))
            <strong>Módulo:</strong> {{ ucfirst($filters['module']) }}<br>
        @endif
        @if(!empty($filters['action']))
            @php
                $actionNames = [
                    'created' => 'Criação',
                    'updated' => 'Atualização',
                    'deleted' => 'Exclusão',
                    'restored' => 'Restauração',
                ];
            @endphp
            <strong>Ação:</strong> {{ $actionNames[$filters['action']] ?? $filters['action'] }}<br>
        @endif
        @if(!empty($filters['date_from']) || !empty($filters['date_to']))
            <strong>Período:</strong> 
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Início' }} 
            até 
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Hoje' }}<br>
        @endif
        <strong>Total de Registros:</strong> {{ $logs->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Data/Hora</th>
                <th style="width: 15%;">Usuário</th>
                <th style="width: 10%;">Ação</th>
                <th style="width: 12%;">Módulo</th>
                <th style="width: 43%;">Descrição</th>
                <th style="width: 10%;">IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                <td>
                    @php
                        $badges = [
                            'created' => 'badge-success',
                            'updated' => 'badge-info',
                            'deleted' => 'badge-danger',
                            'restored' => 'badge-warning',
                        ];
                        $actions = [
                            'created' => 'Criação',
                            'updated' => 'Atualização',
                            'deleted' => 'Exclusão',
                            'restored' => 'Restauração',
                        ];
                    @endphp
                    <span class="badge {{ $badges[$log->action] ?? '' }}">
                        {{ $actions[$log->action] ?? $log->action }}
                    </span>
                </td>
                <td>{{ ucfirst(str_replace('_', ' ', $log->module)) }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
