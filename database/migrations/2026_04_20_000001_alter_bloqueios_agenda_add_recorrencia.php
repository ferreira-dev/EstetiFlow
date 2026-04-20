<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bloqueios_agenda', function (Blueprint $table) {
            // Torna data_inicio e data_fim nullable para suportar bloqueios recorrentes
            // (sem data fixa — valem indefinidamente até serem removidos)
            $table->dateTime('data_inicio')->nullable()->change();
            $table->dateTime('data_fim')->nullable()->change();

            // Campos para bloqueios recorrentes
            $table->boolean('recorrente')->default(false)->after('tipo');
            $table->time('hora_inicio')->nullable()->after('recorrente');   // Ex: "13:00"
            $table->time('hora_fim')->nullable()->after('hora_inicio');     // Ex: "14:00"
            // JSON array de dias da semana (0=Dom … 6=Sáb). null = todos os dias.
            $table->text('dias_semana')->nullable()->after('hora_fim');
        });
    }

    public function down(): void
    {
        Schema::table('bloqueios_agenda', function (Blueprint $table) {
            $table->dropColumn(['recorrente', 'hora_inicio', 'hora_fim', 'dias_semana']);
            $table->dateTime('data_inicio')->nullable(false)->change();
            $table->dateTime('data_fim')->nullable(false)->change();
        });
    }
};
