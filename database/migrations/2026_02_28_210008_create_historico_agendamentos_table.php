<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statusValues = [
            'pendente', 'confirmado', 'em_atendimento', 'concluido',
            'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu',
        ];

        Schema::create('historico_agendamentos', function (Blueprint $table) use ($statusValues) {
            $table->id();
            $table->foreignId('agendamento_id')->constrained('agendamentos')->cascadeOnDelete();
            $table->enum('status_anterior', $statusValues);
            $table->enum('status_novo', $statusValues);
            $table->foreignId('alterado_por_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_agendamentos');
    }
};
