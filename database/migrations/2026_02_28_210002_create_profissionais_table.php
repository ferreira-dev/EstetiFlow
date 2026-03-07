<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profissionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->cascadeOnDelete();
            $table->string('nome_profissional', 150);
            $table->string('especialidade', 150)->nullable();
            $table->string('foto_url', 500)->nullable();
            $table->boolean('aceita_agendamentos')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['usuario_id', 'estabelecimento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profissionais');
    }
};
