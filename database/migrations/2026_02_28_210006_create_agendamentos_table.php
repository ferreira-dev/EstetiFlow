<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->dateTime('data_hora_inicio');
            $table->dateTime('data_hora_fim');
            $table->enum('status', [
                'pendente', 'confirmado', 'em_atendimento', 'concluido',
                'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu',
            ])->default('pendente');
            $table->decimal('valor_total', 10, 2);
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['profissional_id', 'data_hora_inicio', 'data_hora_fim'], 'ag_prof_inicio_fim_idx');
            $table->index(['cliente_id', 'status'], 'ag_cliente_status_idx');
            $table->index(['status', 'data_hora_inicio'], 'ag_status_inicio_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
