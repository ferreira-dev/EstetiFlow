<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK (silently ignore if it doesn't exist)
        try {
            DB::statement('ALTER TABLE profissionais DROP FOREIGN KEY `profissionais_estabelecimento_id_foreign`');
        } catch (\Exception $e) {
            // Already dropped
        }

        // Drop unique index (silently ignore if it doesn't exist)
        try {
            DB::statement('ALTER TABLE profissionais DROP INDEX `profissionais_usuario_id_estabelecimento_id_unique`');
        } catch (\Exception $e) {
            // Already dropped
        }

        // Make the column nullable
        DB::statement('ALTER TABLE profissionais MODIFY `estabelecimento_id` BIGINT UNSIGNED NULL');

        // Recreate FK with ON DELETE SET NULL (silently ignore if already exists)
        try {
            DB::statement('
                ALTER TABLE profissionais
                ADD CONSTRAINT `profissionais_estabelecimento_id_foreign`
                FOREIGN KEY (`estabelecimento_id`)
                REFERENCES `estabelecimentos`(`id`)
                ON DELETE SET NULL
            ');
        } catch (\Exception $e) {
            // FK already exists
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE profissionais DROP FOREIGN KEY `profissionais_estabelecimento_id_foreign`');
        } catch (\Exception $e) {}

        DB::statement('ALTER TABLE profissionais MODIFY `estabelecimento_id` BIGINT UNSIGNED NOT NULL');

        DB::statement('
            ALTER TABLE profissionais
            ADD CONSTRAINT `profissionais_estabelecimento_id_foreign`
            FOREIGN KEY (`estabelecimento_id`)
            REFERENCES `estabelecimentos`(`id`)
            ON DELETE CASCADE
        ');

        DB::statement('ALTER TABLE profissionais ADD UNIQUE `profissionais_usuario_id_estabelecimento_id_unique` (`usuario_id`, `estabelecimento_id`)');
    }
};
