<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorios_financeiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->date('data_referencia');
            $table->enum('periodo_tipo', ['dia', 'semana', 'mes']);
            $table->unsignedInteger('total_agendamentos')->default(0);
            $table->decimal('receita_total', 10, 2)->default(0.00);
            $table->decimal('receita_confirmada', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['profissional_id', 'periodo_tipo', 'data_referencia'], 'rf_prof_periodo_data_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios_financeiros');
    }
};
