<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class ImportTemplateController extends Controller
{
    public function download(string $type)
    {
        $templates = [
            'grupos_educacionais' => [
                 'filename' => 'template_grupos_educacionais.csv',
                 'headers' => ['nome', 'cnpj', 'descricao'],
                'example' => [
                'Grupo Educacional XYZ',
                '12.345.678/0001-90',
                'Grupo educacional focado em tecnologia'
    ]
],
            'mantenedoras' => [
                'filename' => 'template_mantenedoras.csv',
                'headers' => ['razao_social', 'cnpj', 'grupo_educacional_cnpj', 'nome_fantasia', 'inscricao_estadual', 'inscricao_municipal'],
                'example' => [
                    'Mantenedora ABC Ltda',
                    '98.765.432/0001-10',
                    '12.345.678/0001-90',
                    'Mantenedora ABC',
                    '123456789',
                    '987654321'
                ]
            ],
            'instituicoes' => [
    'filename' => 'template_instituicoes.csv',
    'headers' => ['razao_social', 'cnpj', 'mantenedora_cnpj', 'nome_fantasia', 'sigla', 'tipo_organizacao_academica', 'categoria_administrativa', 'codigo_mec', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'],
    'example' => [
        'Universidade Exemplo S.A.',
        '11.222.333/0001-44',
        '98.765.432/0001-10',
        'UniExemplo',
        'UEX',
        'Universidade',
        'Privada',
        '12345',
        'Avenida Paulista',
        '1000',
        'Torre A',
        'Bela Vista',
        'São Paulo',
        'SP',
        '01310-100'
                ]
            ],
            'campi' => [
                'filename' => 'template_campi.csv',
                'headers' => ['nome', 'instituicao_cnpj', 'sigla', 'codigo_inep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep'],
                'example' => [
                    'Campus Central',
                    '11.222.333/0001-44',
                    'CC',
                    '12345678',
                    'Rua das Flores',
                    '123',
                    'Bloco A',
                    'Centro',
                    'São Paulo',
                    'SP',
                    '01234-567'
                ]
            ],
            'setores' => [
                'filename' => 'template_setores.csv',
                'headers' => ['nome', 'tipo', 'descricao', 'nivel'],
                'example' => [
                    'Recursos Humanos',
                    'Corporativo',
                    'Setor responsável pela gestão de pessoas',
                    '1'
                ]
            ]
        ];

        if (!isset($templates[$type])) {
            return response()->json(['message' => 'Tipo de template inválido'], 404);
        }

        $template = $templates[$type];
        
        // Cria conteúdo CSV
        $csv = [];
        $csv[] = implode(',', $template['headers']);
        $csv[] = implode(',', array_map(function($value) {
            return '"' . $value . '"';
        }, $template['example']));

        $content = implode("\n", $csv);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $template['filename'] . '"',
        ]);
    }

    public function list()
    {
        return response()->json([
            [
                'type' => 'grupos_educacionais',
                'label' => 'Grupos Educacionais',
                'description' => 'Template para importação de grupos educacionais',
                'required_fields' => ['nome', 'cnpj'],
                'optional_fields' => [ 'descricao']
            ],
            [
                'type' => 'mantenedoras',
                'label' => 'Mantenedoras',
                'description' => 'Template para importação de mantenedoras',
                'required_fields' => ['razao_social', 'cnpj', 'grupo_educacional_cnpj'],
                'optional_fields' => ['nome_fantasia', 'inscricao_estadual', 'inscricao_municipal']
            ],
            [
                'type' => 'instituicoes',
                'label' => 'Instituições',
                'description' => 'Template para importação de instituições de ensino',
                'required_fields' => ['razao_social', 'cnpj', 'mantenedora_cnpj'],
                'optional_fields' => ['nome_fantasia', 'sigla', 'tipo_organizacao_academica', 'categoria_administrativa', 'codigo_mec']
            ],
            [
                'type' => 'campi',
                'label' => 'Campi',
                'description' => 'Template para importação de campi',
                'required_fields' => ['nome', 'instituicao_cnpj'],
                'optional_fields' => ['sigla', 'codigo_inep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep']
            ],
            [
                'type' => 'setores',
                'label' => 'Setores',
                'description' => 'Template para importação de setores',
                'required_fields' => ['nome', 'tipo'],
                'optional_fields' => ['descricao', 'nivel']
            ]
        ]);
    }
}
