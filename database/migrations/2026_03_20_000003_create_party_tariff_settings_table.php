<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de configuración tarifaria particular por cliente (party).
     *
     * Solo se crea un registro si el cliente tiene condiciones especiales
     * negociadas para una ruta determinada.
     *
     * Si NO existe registro para un cliente → el servicio usa el cuadro
     * tarifario general (tariff_brackets) para calcular el flete.
     *
     * El campo 'billing_mode' determina qué fórmula aplica el servicio:
     * ──────────────────────────────────────────────────────────────────
     *  'kg'              → busca el tramo en tariff_brackets por peso
     *  'tonelada'        → rate_per_ton_custom * (peso_kg / 1000)
     *  'volumen'         → rate_per_m3_custom * m3
     *  'bultos'          → rate_per_bulto * cantidad_bultos
     *  'pallets'         → rate_per_pallet * cantidad_pallets
     *  'valor_declarado' → declared_value_pct % del valor declarado
     * ──────────────────────────────────────────────────────────────────
     *
     * En TODOS los modos se puede definir un importe mínimo (minimum_charge).
     * Si el resultado calculado < minimum_charge → se cobra el mínimo.
     */
    public function up(): void
    {
        Schema::create('party_tariff_settings', function (Blueprint $table) {
            $table->id();

            // Cliente (remitente o destinatario) al que aplica esta configuración
            // Al eliminar un party, se eliminan sus configuraciones tarifarias
            $table->foreignId('party_id')
                ->constrained('parties')
                ->cascadeOnDelete();

            // Cuadro tarifario base al que se vincula este acuerdo
            // Determina la ruta (origen → destino) para la que aplica
            // Al eliminar el cuadro base, se elimina este acuerdo también
            $table->foreignId('tariff_table_id')
                ->constrained('tariff_tables')
                ->cascadeOnDelete();

            // ──────────────────────────────────────────────────────────────
            // MODO DE FACTURACIÓN
            // Define qué fórmula usa el GuiaImporteService para este cliente
            // ──────────────────────────────────────────────────────────────
            $table->enum('billing_mode', [
                'kg',              // Usa los tramos de tariff_brackets
                'tonelada',        // Precio por tonelada personalizado
                'volumen',         // Precio por M3 personalizado
                'bultos',          // Precio por bulto
                'pallets',         // Precio por pallet
                'valor_declarado', // Porcentaje sobre el valor declarado
            ]);

            // ──────────────────────────────────────────────────────────────
            // IMPORTE MÍNIMO — Aplica a TODOS los modos de facturación
            // Si el total calculado es menor a este valor → se cobra minimum_charge
            // null = sin mínimo (se cobra el importe calculado sin piso)
            // ──────────────────────────────────────────────────────────────
            $table->decimal('minimum_charge', 12, 2)->nullable();

            // ──────────────────────────────────────────────────────────────
            // VALORES PARTICULARES POR MODO
            // Solo se completa el campo correspondiente al billing_mode elegido
            // Los demás campos quedan en null
            // ──────────────────────────────────────────────────────────────

            // Para billing_mode = 'tonelada'
            // Precio por tonelada negociado con el cliente
            // Fórmula: (peso_kg / 1000) * rate_per_ton_custom
            // Ej: $120.000 por tonelada
            $table->decimal('rate_per_ton_custom', 12, 2)->nullable();

            // Para billing_mode = 'volumen'
            // Precio por M3 negociado con el cliente
            // Fórmula: m3 * rate_per_m3_custom
            // Si null → usa rate_per_m3 del cuadro general
            $table->decimal('rate_per_m3_custom', 12, 2)->nullable();

            // Para billing_mode = 'bultos'
            // Precio por cada bulto de la guía
            // Fórmula: cantidad_bultos * rate_per_bulto
            $table->decimal('rate_per_bulto', 12, 2)->nullable();

            // Para billing_mode = 'pallets'
            // Precio por cada pallet de la guía
            // Fórmula: cantidad_pallets * rate_per_pallet
            $table->decimal('rate_per_pallet', 12, 2)->nullable();

            // Para billing_mode = 'valor_declarado'
            // Porcentaje aplicado sobre el valor declarado de la mercadería
            // Fórmula: valor_declarado * (declared_value_pct / 100)
            // Ej: 0.5000 = 0.5% del valor declarado
            $table->decimal('declared_value_pct', 8, 4)->nullable();

            // ──────────────────────────────────────────────────────────────
            // VIGENCIA Y NOTAS
            // ──────────────────────────────────────────────────────────────

            // Fecha de inicio de vigencia del acuerdo
            $table->date('valid_from');

            // Fecha de fin de vigencia — null = sin vencimiento
            $table->date('valid_until')->nullable();

            // Notas internas sobre el acuerdo (uso administrativo únicamente)
            $table->text('notes')->nullable();

            $table->timestamps();

            // Un cliente puede tener un solo acuerdo activo por cuadro tarifario
            // Si se necesita cambiar condiciones → valid_until en el anterior y nuevo registro
            $table->unique(['party_id', 'tariff_table_id']);

            // Índice para la consulta principal del GuiaImporteService
            $table->index(['party_id', 'tariff_table_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_tariff_settings');
    }
};
