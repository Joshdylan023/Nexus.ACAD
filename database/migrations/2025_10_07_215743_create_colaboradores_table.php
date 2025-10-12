<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // Vínculo Organizacional e Lotação
            $table->morphs('unidade_organizacional'); // 'empresa' do colaborador
            $table->morphs('unidade_lotacao'); // local de trabalho físico
            $table->foreignId('setor_vinculo_id')->constrained('setor_vinculos'); // setor dentro da lotação

            // Hierarquia
            $table->foreignId('gestor_imediato_id')->nullable()->constrained('colaboradores')->onDelete('set null');
            $table->boolean('is_gestor')->default(false);

            // Credenciais e Dados do Vínculo
            $table->string('matricula_funcional')->unique();
            $table->string('email_funcional')->unique();
            $table->string('password');
            $table->string('cargo');
            $table->date('data_admissao');
            $table->enum('status', ['Ativo', 'Afastado', 'Desligado'])->default('Ativo');

            // Fotos
            $table->string('foto_registro_rh')->nullable()->comment('Foto para registro no RH');
            $table->string('foto_perfil_sistema')->nullable()->comment('Foto para perfil no sistema');

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('colaboradores'); }
};