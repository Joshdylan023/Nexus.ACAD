<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 20px;
        }
        .info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .gestor-card {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
        }
        .gestor-card h3 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-gestor {
            background-color: #4CAF50;
            color: white;
        }
        .badge-colaborador {
            background-color: #2196F3;
            color: white;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>Relatório gerado em: {{ $data }}</p>
        <p>Colaborador: {{ $colaborador }}</p>
    </div>

    @if($gestor)
    <div class="gestor-card">
        <h3>⭐ {{ $is_gestor ? 'Meu Gestor Direto' : 'Meu Gestor' }}</h3>
        <p><strong>Nome:</strong> {{ $gestor['nome'] }}</p>
        <p><strong>Cargo:</strong> {{ $gestor['cargo'] }}</p>
        <p><strong>Email:</strong> {{ $gestor['email'] }}</p>
        @if($gestor['telefone'])
        <p><strong>Telefone:</strong> {{ $gestor['telefone'] }}</p>
        @endif
        @if($gestor['setor'])
        <p><strong>Setor:</strong> {{ $gestor['setor'] }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cargo</th>
                <th>Matrícula</th>
                <th>Email</th>
                <th>Setor</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $item)
            <tr>
                <td>{{ $item['nome'] }}</td>
                <td>{{ $item['cargo'] }}</td>
                <td>{{ $item['matricula'] }}</td>
                <td>{{ $item['email'] }}</td>
                <td>{{ $item['setor'] ?? 'Não informado' }}</td>
                <td>
                    @if($item['is_gestor'])
                        <span class="badge badge-gestor">Gestor</span>
                    @else
                        <span class="badge badge-colaborador">Colaborador</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total de {{ count($dados) }} pessoa(s)</p>
        <p>Nexus.ACAD - Sistema de Gestão Acadêmica</p>
    </div>
</body>
</html>
