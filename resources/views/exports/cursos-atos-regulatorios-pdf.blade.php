<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atos Regulatórios - {{ $curso->nome }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        /* ✅ CABEÇALHO COM LOGO E CORES DA IES */
        .header {
            background: linear-gradient(135deg, 
                {{ $identidade->cor_primaria ?? '#34495e' }} 0%, 
                {{ $identidade->cor_secundaria ?? '#2c3e50' }} 100%
            );
            padding: 30px 20px;
            color: white;
            border-radius: 0 0 15px 15px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-container {
            display: table-cell;
            width: 150px;
            vertical-align: middle;
        }
        
        .logo {
            max-width: 120px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .header-text {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 20px;
        }
        
        .header-text h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
        }
        
        .header-text h2 {
            font-size: 16px;
            font-weight: normal;
            opacity: 0.95;
            color: white;
            margin-bottom: 8px;
        }
        
        .instituicao-nome {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
            color: white;
        }
        
        /* ✅ INFORMAÇÕES DO CURSO */
        .curso-info {
            background: #f8f9fa;
            border-left: 4px solid {{ $identidade->cor_primaria ?? '#34495e' }};
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .curso-info h3 {
            color: {{ $identidade->cor_primaria ?? '#34495e' }};
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .curso-info p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .curso-info strong {
            color: #2c3e50;
        }
        
        /* ✅ TABELA */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        thead {
            background: {{ $identidade->cor_primaria ?? '#34495e' }};
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* ✅ BADGES */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }
        
        .badge-primary {
            background-color: {{ $identidade->cor_primaria ?? '#3498db' }};
            color: white;
        }
        
        .badge-success {
            background-color: #27ae60;
            color: white;
        }
        
        .badge-info {
            background-color: {{ $identidade->cor_secundaria ?? '#16a085' }};
            color: white;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        /* ✅ RODAPÉ */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid {{ $identidade->cor_primaria ?? '#34495e' }};
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        /* ✅ PÁGINA */
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <!-- ✅ CABEÇALHO COM LOGO -->
    <div class="header">
        <div class="header-content">
            @if($identidade && $identidade->logo_principal && $identidade->usar_logo_documentos)
                <div class="logo-container">
                    @php
                        $logoPath = public_path('storage/' . $identidade->logo_principal);
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" class="logo">
                    @endif
                </div>
            @endif
            
            <div class="header-text">
                <h1>Relatório de Atos Regulatórios</h1>
                <h2>Curso: {{ $curso->nome ?? 'Sem Nome' }}</h2>
                @if($curso->instituicao)
                    <p class="instituicao-nome">{{ $curso->instituicao->nome }}</p>
                @endif
            </div>
            
            @if($identidade && $identidade->logo_principal && $identidade->usar_logo_documentos)
                <div class="logo-container"></div> <!-- Espaçamento simétrico -->
            @endif
        </div>
    </div>
    
    <!-- ✅ INFORMAÇÕES DO CURSO -->
    <div class="curso-info">
        <h3>Informações do Curso</h3>
        <p><strong>Código IES:</strong> {{ $curso->codigo_ies ?? 'N/A' }}</p>
        <p><strong>Nível:</strong> {{ $curso->nivel ?? 'N/A' }}</p>
        <p><strong>Modalidade:</strong> {{ $curso->modalidade ? ucfirst($curso->modalidade) : 'N/A' }}</p>
        @if($curso->campus)
            <p><strong>Campus:</strong> {{ $curso->campus->nome }}</p>
        @endif
        @if($curso->coordenador)
            <p><strong>Coordenador(a):</strong> {{ $curso->coordenador->name ?? 'N/A' }}</p>
        @endif
    </div>
    
    <!-- ✅ TABELA DE ATOS -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 18%;">Tipo</th>
                <th style="width: 15%;">Códigos MEC</th>
                <th style="width: 22%;">Portaria</th>
                <th style="width: 15%;">Publicação</th>
                <th style="width: 15%;">Validade</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($atos as $ato)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $ato->id }}</td>
                    <td>
                        <span class="badge 
                            @if($ato->tipo_ato === 'Autorização') badge-primary
                            @elseif($ato->tipo_ato === 'Reconhecimento') badge-success
                            @else badge-info
                            @endif">
                            {{ $ato->tipo_ato }}
                        </span>
                    </td>
                    <td>
                        <strong>MEC:</strong> {{ $ato->codigo_mec }}<br>
                        @if($ato->codigo_emec)
                            <strong>e-MEC:</strong> {{ $ato->codigo_emec }}
                        @endif
                    </td>
                    <td>{{ $ato->numero_portaria }}</td>
                    <td>{{ \Carbon\Carbon::parse($ato->data_publicacao_dou)->format('d/m/Y') }}</td>
                    <td>
                        @if($ato->data_validade_ato)
                            {{ \Carbon\Carbon::parse($ato->data_validade_ato)->format('d/m/Y') }}
                        @else
                            <span style="color: #7f8c8d; font-style: italic;">Sem validade</span>
                        @endif
                    </td>
                    <td>
                        @if($ato->data_validade_ato)
                            @php
                                $dataValidade = \Carbon\Carbon::parse($ato->data_validade_ato);
                                $hoje = \Carbon\Carbon::now();
                                $diasRestantes = $hoje->diffInDays($dataValidade, false);
                            @endphp
                            
                            @if($diasRestantes < 0)
                                <span class="badge badge-danger">VENCIDO</span>
                            @elseif($diasRestantes <= 90)
                                <span class="badge badge-warning">{{ $diasRestantes }}d</span>
                            @else
                                <span class="badge badge-success">ATIVO</span>
                            @endif
                        @else
                            <span class="badge" style="background: #95a5a6;">N/A</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d; font-style: italic;">
                        Nenhum ato regulatório cadastrado para este curso.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- ✅ RODAPÉ -->
    <div class="footer">
        <p><strong>Relatório gerado em:</strong> {{ now()->format('d/m/Y \à\s H:i:s') }}</p>
        <p><strong>Total de atos:</strong> {{ $atos->count() }}</p>
        @if($curso->instituicao)
            <p>{{ $curso->instituicao->nome_fantasia }} - Sistema Acadêmico</p>
        @endif
    </div>
</body>
</html>
