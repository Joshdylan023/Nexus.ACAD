<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacos_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('andar_id')->constrained('andares')->onDelete('cascade');
            
            // Identificação
            $table->string('codigo', 50); // Ex: "A101", "LAB-01"
            $table->string('nome'); // Ex: "Sala 101", "Laboratório de Informática 1"
            
            // Tipo de Espaço
            $table->enum('tipo', [
                'Sala de Aula',
                'Laboratório',
                'Auditório',
                'Biblioteca',
                'Sala de Reunião',
                'Sala de Professores',
                'Coordenação',
                'Diretoria',
                'Secretaria',
                'Almoxarifado',
                'Banheiro',
                'Copa/Cozinha',
                'Área de Convivência',
                'Estacionamento',
                'Quadra Esportiva',
                'Ginásio',
                'Cantina',
                'Outro'
            ]);
            
            // Características Físicas
            $table->decimal('area', 10, 2)->nullable(); // m²
            $table->integer('capacidade')->nullable(); // pessoas
            $table->integer('capacidade_exame')->nullable(); // capacidade reduzida para provas
            
            // Infraestrutura
            $table->boolean('ar_condicionado')->default(false);
            $table->boolean('projetor')->default(false);
            $table->boolean('lousa_digital')->default(false);
            $table->boolean('computadores')->default(false);
            $table->integer('quantidade_computadores')->nullable();
            $table->boolean('wifi')->default(false);
            $table->boolean('acessibilidade')->default(false);
            $table->boolean('cameras_seguranca')->default(false);
            $table->boolean('sistema_som')->default(false);
            
            // Mobiliário
            $table->integer('quantidade_carteiras')->nullable();
            $table->integer('quantidade_cadeiras')->nullable();
            $table->integer('quantidade_mesas')->nullable();
            $table->string('tipo_mobiliario')->nullable(); // "Fixo", "Móvel", "Bancadas"
            
            // Uso e Disponibilidade
            $table->enum('status', ['Disponível', 'Ocupado', 'Manutenção', 'Reforma', 'Indisponível'])->default('Disponível');
            $table->boolean('permite_reserva')->default(true);
            $table->json('horarios_disponiveis')->nullable(); // Ex: {"seg": "07:00-22:00", ...}
            
            // Responsabilidade
            $table->foreignId('responsavel_id')->nullable()->constrained('colaboradores')->nullOnDelete();
            
            // Mídias e Documentos
            $table->json('fotos')->nullable();
            $table->json('videos_360')->nullable(); // URLs de vídeos 360°
            $table->json('documentos')->nullable();
            $table->json('equipamentos')->nullable(); // Lista de equipamentos detalhada
            
            // Observações
            $table->text('observacoes')->nullable();
            $table->text('restricoes')->nullable(); // Ex: "Apenas para pós-graduação"
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['andar_id', 'codigo']);
            $table->index('tipo');
            $table->index('status');
            $table->index('responsavel_id');
            
            // Unique por andar
            $table->unique(['andar_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacos_fisicos');
    }
};
