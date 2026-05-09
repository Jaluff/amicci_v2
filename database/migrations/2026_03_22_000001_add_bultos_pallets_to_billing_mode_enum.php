<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los cambios de esta migración fueron aplicados manualmente via MySQL:
     *
     * 1. Se amplió el ENUM billing_mode para incluir 'bultos_pallets'.
     * 2. Se eliminó el UNIQUE(party_id, tariff_table_id) y se reemplazó
     *    por UNIQUE(party_id) — un acuerdo tarifario por cliente.
     * 3. El FK de party_id fue recreado correctamente.
     *
     * Esta migración es idempotente — verifica el estado antes de actuar.
     */
    public function up(): void
    {
        // Verificar si 'bultos_pallets' ya está en el ENUM
        $column = DB::select("
            SELECT COLUMN_TYPE FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'party_tariff_settings'
              AND COLUMN_NAME  = 'billing_mode'
        ");

        if (! str_contains($column[0]->COLUMN_TYPE ?? '', 'bultos_pallets')) {
            DB::statement("
                ALTER TABLE party_tariff_settings
                MODIFY COLUMN billing_mode ENUM(
                    'kg', 'tonelada', 'volumen',
                    'bultos', 'pallets', 'bultos_pallets',
                    'valor_declarado'
                ) NOT NULL
            ");
        }

        // Verificar si el unique compuesto viejo existe y eliminarlo
        $oldUnique = DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'party_tariff_settings'
              AND INDEX_NAME   = 'party_tariff_settings_party_id_tariff_table_id_unique'
            LIMIT 1
        ");

        if (! empty($oldUnique)) {
            // Primero soltar el FK que lo bloquea
            try {
                DB::statement('ALTER TABLE party_tariff_settings DROP FOREIGN KEY party_tariff_settings_party_id_foreign');
            } catch (Throwable) {
            }

            DB::statement('ALTER TABLE party_tariff_settings DROP INDEX party_tariff_settings_party_id_tariff_table_id_unique');

            // Re-crear el FK
            DB::statement('ALTER TABLE party_tariff_settings ADD CONSTRAINT party_tariff_settings_party_id_foreign FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE');
        }

        // Agregar el nuevo unique(party_id) si no existe
        $newUnique = DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'party_tariff_settings'
              AND INDEX_NAME   = 'pts_party_unique'
            LIMIT 1
        ");

        if (empty($newUnique)) {
            DB::statement('ALTER TABLE party_tariff_settings ADD UNIQUE KEY pts_party_unique (party_id)');
        }
    }

    public function down(): void
    {
        // No revertimos: la estructura anterior requería tariff_table_id
        // que ya fue eliminado del diseño del sistema.
    }
};
