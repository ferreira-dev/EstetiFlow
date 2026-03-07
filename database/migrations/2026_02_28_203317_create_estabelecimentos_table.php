<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estabelecimentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia', 150);
            $table->char('cnpj', 14)->nullable()->unique();
            $table->text('descricao')->nullable();
            $table->string('foto_capa_url', 500)->nullable();
            $table->string('telefone_principal', 20)->nullable();
            $table->string('telefone_secundario', 20)->nullable();
            $table->char('cep', 8)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cidade', 'estado', 'ativo']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estabelecimentos');
    }
};
