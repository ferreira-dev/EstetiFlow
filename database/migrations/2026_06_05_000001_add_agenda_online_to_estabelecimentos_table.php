<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->boolean('agenda_online')
                ->default(true)
                ->after('url_personalizada')
                ->comment('Controla se os clientes podem agendar online. Quando false, o botão Reservar redireciona para o WhatsApp.');
        });
    }

    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('agenda_online');
        });
    }
};
