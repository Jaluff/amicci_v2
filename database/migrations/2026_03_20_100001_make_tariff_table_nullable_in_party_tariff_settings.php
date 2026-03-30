<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hace nullable la FK tariff_table_id en party_tariff_settings.
     *
     * Razón del cambio de diseño:
     *   El cuadro tarifario (tariff_table) ya NO se configura en el cliente.
     *   Se determina automáticamente en el GuiaImporteService usando el
     *   origen y destino de la guía. El cliente solo define el MODO de
     *   facturación y los valores particulares.
     */
    public function up(): void
    {
        Schema::table('party_tariff_settings', function (Blueprint $table) {
            // 1. Primero eliminamos la FK constraint existente
            $table->dropForeign(['tariff_table_id']);

            // 2. Hacemos nullable la columna
            $table->unsignedBigInteger('tariff_table_id')->nullable()->change();

            // 3. Re-creamos la FK pero ahora nullableOnDelete
            $table->foreign('tariff_table_id')
                ->references('id')
                ->on('tariff_tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('party_tariff_settings', function (Blueprint $table) {
            $table->dropForeign(['tariff_table_id']);
            $table->unsignedBigInteger('tariff_table_id')->nullable(false)->change();
            $table->foreign('tariff_table_id')
                ->references('id')
                ->on('tariff_tables')
                ->cascadeOnDelete();
        });
    }
};
