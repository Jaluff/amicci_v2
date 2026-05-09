<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de cuadros tarifarios base.
     *
     * Cada registro representa una tarifa para una ruta específica (origen → destino).
     * El destino es un valor completo que distingue zonas, por ejemplo:
     *   - "Mendoza Este"
     *   - "Mendoza Sur"
     *   - "Buenos Aires"
     *
     * Esta tabla contiene la tarifa general de tonelada y aforo por M3.
     * Los tramos de peso (escala por kg) están en la tabla tariff_brackets.
     */
    public function up(): void
    {
        Schema::create('tariff_tables', function (Blueprint $table) {
            $table->id();

            // Nombre descriptivo del cuadro tarifario
            // Ej: "Buenos Aires → Mendoza Este"
            $table->string('name');

            // Ciudad o zona de origen del servicio de transporte
            // Ej: "Buenos Aires", "Mendoza"
            $table->string('origin');

            // Ciudad o zona de destino del servicio de transporte
            // El destino incluye la zona si aplica, eliminando el campo 'zone'
            // Ej: "Mendoza Este", "Mendoza Sur", "Buenos Aires"
            $table->string('destination');

            // Tarifa por tonelada para envíos que superan los 1000 kg
            // Fórmula: (peso_en_kg / 1000) * rate_per_ton
            // Ej: $145.031 por tonelada
            $table->decimal('rate_per_ton', 12, 2);

            // Tarifa de aforo por metro cúbico (M3)
            // Se aplica cuando el "peso por volumen" supera al peso real
            // Ej: $60.540 por M3
            $table->decimal('rate_per_m3', 12, 2);

            // Fecha de vigencia del cuadro tarifario
            // valid_until = null significa "vigente sin fecha de vencimiento"
            $table->date('valid_from');
            $table->date('valid_until')->nullable();

            // Permite desactivar un cuadro sin eliminarlo
            // Un cuadro inactivo no se usa en el cálculo de la guía
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índice compuesto para búsqueda rápida por ruta
            $table->index(['origin', 'destination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_tables');
    }
};
