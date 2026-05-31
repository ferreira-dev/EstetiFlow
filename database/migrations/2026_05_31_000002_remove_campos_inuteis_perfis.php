<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove campos sem uso funcional no MVP da tabela perfis.
     * Campos removidos: genero, cep, logradouro, numero, complemento,
     * bairro, cidade, estado, latitude, longitude.
     *
     * Dados de endereço do cliente não são necessários para o fluxo de
     * agendamento estético. Geolocalização do cliente pode ser obtida
     * via GPS do browser no futuro sem necessidade de endereço manual.
     */
    public function up(): void
    {
        Schema::table('perfis', function (Blueprint $table) {
            $table->dropColumn([
                'genero',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'estado',
                'latitude',
                'longitude',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('perfis', function (Blueprint $table) {
            $table->enum('genero', ['masculino', 'feminino', 'outro', 'prefiro_nao_informar'])->nullable();
            $table->char('cep', 8)->nullable();
            $table->string('logradouro', 200)->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->char('estado', 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });
    }
};
