<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->json('caracteristicas')
                ->nullable()
                ->after('descricao')
                ->comment('Chaves das comodidades marcadas pelo dono (ver Estabelecimento::CARACTERISTICAS), exibidas na página pública.');
        });
    }

    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('caracteristicas');
        });
    }
};
