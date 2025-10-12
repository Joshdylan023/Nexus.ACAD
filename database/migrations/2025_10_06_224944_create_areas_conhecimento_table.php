<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('areas_conhecimento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grande_area_conhecimento_id')->constrained('grandes_areas_conhecimento');
            $table->string('nome')->unique();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('areas_conhecimento'); }
};