<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->string('url_personalizada', 160)->nullable()->unique()->after('nome_fantasia');
        });

        $slugsEmUso = [];

        DB::table('estabelecimentos')
            ->select('id', 'nome_fantasia')
            ->orderBy('id')
            ->get()
            ->each(function (object $estabelecimento) use (&$slugsEmUso): void {
                $baseSlug = Str::slug($estabelecimento->nome_fantasia) ?: "estabelecimento-{$estabelecimento->id}";
                $slug = $baseSlug;
                $sufixo = 2;

                while (isset($slugsEmUso[$slug])) {
                    $slug = "{$baseSlug}-{$sufixo}";
                    $sufixo++;
                }

                $slugsEmUso[$slug] = true;

                DB::table('estabelecimentos')
                    ->where('id', $estabelecimento->id)
                    ->update(['url_personalizada' => $slug]);
            });

        DB::statement('ALTER TABLE estabelecimentos MODIFY url_personalizada VARCHAR(160) NOT NULL');
    }

    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropUnique('estabelecimentos_url_personalizada_unique');
            $table->dropColumn('url_personalizada');
        });
    }
};
