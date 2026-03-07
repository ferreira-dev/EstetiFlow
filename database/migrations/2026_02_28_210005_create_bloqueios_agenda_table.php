<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueios_agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->string('motivo', 255)->nullable();
            $table->enum('tipo', ['ferias', 'folga', 'almoco', 'outro'])->default('outro');
            $table->timestamps();

            $table->index(['profissional_id', 'data_inicio', 'data_fim'], 'bloq_prof_inicio_fim_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueios_agenda');
    }
};
