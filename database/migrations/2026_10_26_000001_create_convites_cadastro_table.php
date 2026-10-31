<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convites de "completar cadastro" gerados pelo profissional para clientes pré-cadastrados.
     * O token é aleatório e só o hash (sha256) é armazenado; expira e é de uso único.
     */
    public function up(): void
    {
        Schema::create('convites_cadastro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('criado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expira_em');
            $table->timestamp('usado_em')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'usado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convites_cadastro');
    }
};
