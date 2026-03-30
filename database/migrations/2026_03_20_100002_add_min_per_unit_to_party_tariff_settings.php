<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega mínimos independientes para bultos y pallets.
     *
     * Razón: cuando el modo es bultos/pallets (o sus combinaciones),
     * cada unidad puede tener su propio importe mínimo de cobro.
     * El campo 'minimum_charge' existente se usa para los demás modos
     * (kg, tonelada, volumen, valor_declarado).
     */
    public function up(): void
    {
        Schema::table('party_tariff_settings', function (Blueprint $table) {
            // Mínimo por carga de bultos
            $table->decimal('minimum_per_bulto', 10, 2)
                ->nullable()
                ->after('rate_per_bulto')
                ->comment('Importe mínimo para el concepto bultos');

            // Mínimo por carga de pallets
            $table->decimal('minimum_per_pallet', 10, 2)
                ->nullable()
                ->after('rate_per_pallet')
                ->comment('Importe mínimo para el concepto pallets');
        });
    }

    public function down(): void
    {
        Schema::table('party_tariff_settings', function (Blueprint $table) {
            $table->dropColumn(['minimum_per_bulto', 'minimum_per_pallet']);
        });
    }
};
