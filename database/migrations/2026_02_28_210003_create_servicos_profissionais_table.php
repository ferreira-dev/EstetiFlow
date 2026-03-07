<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicos_profissionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->decimal('preco', 10, 2);
            $table->unsignedSmallInteger('tempo_execucao_minutos');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['profissional_id', 'servico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos_profissionais');
    }
};
