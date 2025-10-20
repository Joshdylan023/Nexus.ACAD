<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Cursos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        
        /* ✅ CABEÇALHO COM IDENTIDADE VISUAL */
        .header {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            overflow: hidden;
        }
        
        .header-left {
            float: left;
            width: 25%;
        }
        
        .header-center {
            float: left;
            width: 50%;
            text-align: center;
            padding-top: 10px;
        }
        
        .header-right {
            float: right;
            width: 25%;
            text-align: right;
            padding-top: 10px;
        }
        
        .logo {
            max-height: 80px;
            max-width: 200px;
            display: block;
        }
        
        .titulo-relatorio {
            font-size: 20px;
            font-weight: bold;
            color: {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            margin-bottom: 5px;
        }
        
        .subtitulo-relatorio {
            font-size: 12px;
            color: #666;
        }
        
        .instituicao-nome {
            font-size: 14px;
            font-weight: bold;
            color: {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            margin-top: 8px;
        }
        
        .data-geracao {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* ✅ INFORMAÇÕES DO RELATÓRIO */
        .info-box {
            background: #f5f5f5;
            padding: 12px;
            margin-top: 20px;
            margin-bottom: 15px;
            border-left: 4px solid {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
        }
        
        .info-title {
            font-size: 11px;
            font-weight: bold;
            color: {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            margin-bottom: 8px;
        }
        
        .info-row {
            margin-bottom: 5px;
            font-size: 9px;
        }
        
        .info-label {
            display: inline-block;
            width: 30%;
            font-weight: bold;
            color: #666;
        }
        
        .info-value {
            display: inline-block;
            width: 68%;
            color: #333;
        }
        
        /* ✅ TABELA */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 8px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* ✅ BADGES DE STATUS */
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
            white-space: nowrap;
        }
        
        .badge-success { 
            background: {{ $identidadeVisual->cor_secundaria ?? '#10b981' }}; 
            color: white; 
        }
        .badge-warning { 
            background: #f59e0b; 
            color: white; 
        }
        .badge-danger { 
            background: #ef4444; 
            color: white; 
        }
        .badge-secondary { 
            background: #6b7280; 
            color: white; 
        }
        
        /* ✅ RODAPÉ */
        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 2px solid {{ $identidadeVisual->cor_primaria ?? '#667eea' }};
            text-align: center;
            font-size: 8px;
            color: #999;
        }
        
        .footer-instituicao {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- ✅ CABEÇALHO COM LOGO E IDENTIDADE VISUAL -->
    <div class="header clearfix">
        <!-- Logo no canto esquerdo -->
<div class="header-left">
    @if($identidadeVisual && ($identidadeVisual->logo_principal ?? $identidadeVisual->logo_horizontal ?? null))
        @php
            // ✅ Usa logo_principal ou logo_horizontal
            $logoField = $identidadeVisual->logo_principal ?? $identidadeVisual->logo_horizontal;
            $logoPath = storage_path('app/public/' . $logoField);
            $logoData = null;
            
            if (file_exists($logoPath)) {
                try {
                    $imageContent = file_get_contents($logoPath);
                    $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    
                    // Mapear extensões para MIME types
                    $mimeTypes = [
                        'jpg' => 'jpeg',
                        'jpeg' => 'jpeg',
                        'png' => 'png',
                        'gif' => 'gif',
                        'svg' => 'svg+xml',
                        'webp' => 'webp'
                    ];
                    
                    $mimeType = $mimeTypes[$extension] ?? 'png';
                    $logoData = 'data:image/' . $mimeType . ';base64,' . base64_encode($imageContent);
                } catch (\Exception $e) {
                    // Log do erro se necessário
                }
            }
        @endphp
        
        @if($logoData)
            <img src="{{ $logoData }}" alt="Logo" class="logo">
        @endif
    @endif
</div>
        
        <!-- Título centralizado -->
        <div class="header-center">
            <div class="titulo-relatorio">Relatório de Cursos</div>
            @if($instituicao)
                <div class="subtitulo-relatorio">{{ $instituicao->nome_fantasia }}</div>
            @endif
        </div>
        
        <!-- Informações no canto direito -->
        <div class="header-right">
            <div class="instituicao-nome">Sistema Nexus.ACAD</div>
            <div class="data-geracao">Gerado em: {{ $data_geracao }}</div>
        </div>
    </div>

    <!-- ✅ INFORMAÇÕES DO RELATÓRIO -->
    <div class="info-box">
        <div class="info-title">Filtros Aplicados:</div>
        
        <div class="info-row">
            <span class="info-label">Instituição:</span>
            <span class="info-value">{{ $filtros['instituicao'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Nível:</span>
            <span class="info-value">{{ $filtros['nivel'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Modalidade:</span>
            <span class="info-value">{{ $filtros['modalidade'] }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Total de Cursos:</span>
            <span class="info-value"><strong>{{ $cursos->count() }} curso(s)</strong></span>
        </div>
    </div>

    <!-- ✅ TABELA DE CURSOS -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%">ID</th>
                <th style="width: 9%">Código</th>
                <th style="width: 23%">Nome</th>
                <th style="width: 16%">Instituição</th>
                <th style="width: 10%">Nível</th>
                <th style="width: 10%">Modalidade</th>
                <th style="width: 16%">Coordenador</th>
                <th style="width: 12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cursos as $curso)
            <tr>
                <td style="text-align: center;">{{ $curso->id }}</td>
                <td>{{ $curso->codigo_ies }}</td>
                <td><strong>{{ $curso->nome }}</strong></td>
                <td>{{ $curso->instituicao->nome_fantasia ?? 'N/A' }}</td>
                <td>{{ $curso->nivel }}</td>
                <td>{{ ucfirst($curso->modalidade) }}</td>
                <td>{{ $curso->coordenador->name ?? 'Sem coordenador' }}</td>
                <td style="text-align: center;">
                    <span class="badge 
                        @if($curso->status == 'Ativo') badge-success
                        @elseif($curso->status == 'Em Extinção') badge-warning
                        @elseif($curso->status == 'Extinto') badge-danger
                        @else badge-secondary
                        @endif
                    ">
                        {{ $curso->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 25px; color: #999; font-size: 10px;">
                    <strong>Nenhum curso encontrado com os filtros aplicados.</strong>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ✅ RODAPÉ -->
    <div class="footer">
        @if($instituicao)
            <div class="footer-instituicao">{{ $instituicao->nome_fantasia }}</div>
            @if($instituicao->endereco_sede)
                <div style="margin-bottom: 5px;">{{ $instituicao->endereco_sede }}</div>
            @endif
        @endif
        <div>Sistema Nexus.ACAD - Gestão Acadêmica Integrada</div>
    </div>
</body>
</html>
