<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Prédios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* ✅ CABEÇALHO COM ESTRUTURA DE TABELA */
        .header-wrapper {
            padding: 15px;
            border-bottom: 3px solid {{ $identidadeVisual?->cor_primaria ?? '#3e60f9' }};
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 2px 5px;
        }

        /* Coluna da logo */
        .logo-col {
            width: 140px;
            text-align: left;
        }

        .logo {
            max-height: 60px;
            max-width: 140px;
            object-fit: contain;
        }

        /* Coluna central (título) */
        .title-col {
            text-align: center;
            vertical-align: middle;
        }

        .title-col h1 {
            font-size: 22px;
            color: {{ $identidadeVisual?->cor_primaria ?? '#3e60f9' }};
            margin: 0;
            font-weight: 700;
            line-height: 1.2;
        }

        .title-col p {
            font-size: 13px;
            color: #666;
            margin: 5px 0 0 0;
            font-weight: 500;
        }

        /* Coluna da direita (dados) */
        .info-col {
            width: 220px;
            text-align: right;
            font-size: 10px;
            color: #333;
            line-height: 1.6;
        }

        .info-col p {
            margin: 2px 0;
        }

        .info-col strong {
            color: #000;
            font-weight: 600;
        }

        /* Filtros */
        .filtros {
            background: #f3f4f6;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-left: 4px solid {{ $identidadeVisual?->cor_primaria ?? '#3e60f9' }};
        }

        .filtros h3 {
            font-size: 11px;
            margin-bottom: 10px;
            color: {{ $identidadeVisual?->cor_primaria ?? '#3e60f9' }};
            font-weight: 700;
        }

        .filtros-content {
            font-size: 10px;
            color: #333;
        }

        .filtros-content strong {
            color: #000;
            font-weight: 600;
        }

        /* Tabela */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        table.data-table thead {
            background: {{ $identidadeVisual?->cor_primaria ?? '#3e60f9' }};
            color: white;
        }

        table.data-table thead th {
            padding: 10px 8px;
            font-size: 9px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.data-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        table.data-table tbody td {
            padding: 8px;
            font-size: 10px;
            color: #333;
            vertical-align: top;
        }

        table.data-table tbody td strong {
            font-weight: 600;
            color: #000;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-ativo { background: #d1fae5; color: #065f46; }
        .badge-inativo { background: #e5e7eb; color: #374151; }
        .badge-manutenção { background: #fef3c7; color: #92400e; }

        /* Rodapé */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #666;
            text-align: center;
            background: #fff;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <!-- ✅ CABEÇALHO COM TABELA (3 linhas) -->
    <div class="header-wrapper">
        <table class="header-table">
            <tr>
                <!-- Logo (esquerda) -->
                <td class="logo-col" rowspan="3">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
                    @endif
                </td>
                
                <!-- Título (centro) -->
                <td class="title-col" rowspan="2">
                    <h1>{{ $instituicao?->nome_fantasia ?? $instituicao?->razao_social ?? 'Nexus ACAD' }}</h1>
                    <p>Relatório de Prédios</p>
                </td>
                
                <!-- Data (direita - linha 1) -->
                <td class="info-col">
                    <p><strong>Data:</strong> {{ $dataGeracao }}</p>
                </td>
            </tr>
            <tr>
                <!-- Gerado por (direita - linha 2) -->
                <td class="info-col">
                    <p><strong>Gerado por:</strong> {{ $usuario }}</p>
                </td>
            </tr>
            <tr>
                <!-- Espaço vazio (centro) -->
                <td></td>
                
                <!-- Total (direita - linha 3) -->
                <td class="info-col">
                    <p><strong>Total de prédios:</strong> {{ $predios->count() }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Filtros -->
    @if(array_filter($filtros))
    <div class="filtros">
        <h3>? Filtros Aplicados</h3>
        <div class="filtros-content">
            <strong>Instituição:</strong> {{ $filtros['instituicao'] ?? 'Todas' }}
            @if($filtros['campus']) | <strong>Campus:</strong> {{ $filtros['campus'] }} @endif
        </div>
    </div>
    @endif

    <!-- Tabela de Prédios -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">CÓDIGO</th>
                <th style="width: 25%;">NOME</th>
                <th style="width: 25%;">CAMPUS</th>
                <th style="width: 12%;" class="text-center">BLOCOS</th>
                <th style="width: 12%;" class="text-center">ANDARES</th>
                <th style="width: 16%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($predios as $predio)
            <tr>
                <td><strong>{{ $predio->codigo ?? '-' }}</strong></td>
                <td><strong>{{ $predio->nome }}</strong></td>
                <td>{{ $predio->campus?->nome ?? '-' }}</td>
                <td class="text-center">{{ $predio->blocos_count ?? $predio->blocos->count() }}</td>
                <td class="text-center">
                    @php
                        $totalAndares = 0;
                        foreach($predio->blocos as $bloco) {
                            $totalAndares += $bloco->andares_count ?? $bloco->andares->count();
                        }
                    @endphp
                    {{ $totalAndares }}
                </td>
                <td>
                    <span class="badge badge-{{ strtolower($predio->status ?? 'ativo') }}">
                        {{ $predio->status ?? 'Ativo' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                    Nenhum prédio encontrado com os filtros aplicados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Rodapé -->
    <div class="footer">
        © {{ date('Y') }} {{ $identidadeVisual?->texto_rodape ?? 'Nexus.ACAD - Sistema de Gestão Acadêmica' }} | Documento gerado automaticamente em {{ $dataGeracao }}
    </div>
</body>
</html>
