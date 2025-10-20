<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservas_espacos', function (Blueprint $table) {
            $table->string('finalidade')->nullable()->after('descricao');
        });
    }

    public function down()
    {
        Schema::table('reservas_espacos', function (Blueprint $table) {
            $table->dropColumn('finalidade');
        });
    }
};
