<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->string('cor_primaria', 20)
                ->default('emerald')
                ->after('agenda_online')
                ->comment('Nome da paleta primária do tema Aura (PrimeVue) aplicada na página pública e no painel do estabelecimento.');
        });
    }

    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('cor_primaria');
        });
    }
};
