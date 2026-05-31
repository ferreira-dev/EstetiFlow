<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Torna o email nullable para suportar pré-cadastros feitos pelo profissional.
     * O MySQL 8 trata múltiplos NULLs como distintos em colunas UNIQUE,
     * portanto a constraint de unicidade permanece funcional para emails preenchidos.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable(false)->change();
        });
    }
};
