<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de tramos de peso (escala tarifaria por kg).
     *
     * Cada registro representa un tramo de la escala:
     *   "De X kg A Y kg = $valor"
     *
     * Cada cuadro tarifario (tariff_table) tiene su propio conjunto de tramos
     * con los mismos rangos de peso pero distintos valores monetarios.
     *
     * Ejemplo de tramos para "BA → Mendoza Este":
     *   De 1  a  20 kg = $16.649
     *   De 21 a  30 kg = $18.442
     *   ...
     *   De 751 a 999 kg = $133.382
     *
     * Para envíos >= 1000 kg se usa directamente rate_per_ton de tariff_tables.
     */
    public function up(): void
    {
        Schema::create('tariff_brackets', function (Blueprint $table) {
            $table->id();

            // FK al cuadro tarifario al que pertenece este tramo
            // Al eliminar un cuadro tarifario, sus tramos se eliminan en cascada
            $table->foreignId('tariff_table_id')
                ->constrained('tariff_tables')
                ->cascadeOnDelete();

            // Límite inferior del tramo expresado en kilogramos enteros
            // Ej: 1, 21, 31, 41, 51... 751
            $table->unsignedInteger('weight_from');

            // Límite superior del tramo expresado en kilogramos enteros
            // Ej: 20, 30, 40, 50... 999
            $table->unsignedInteger('weight_to');

            // Precio fijo del tramo en pesos
            // Este es el importe total a cobrar cuando el peso cae en este tramo
            // Ej: $16.649, $18.442, $133.382
            $table->decimal('rate', 12, 2);

            $table->timestamps();

            // Índice compuesto para localizar el tramo correcto por peso
            // Se usa en la consulta: WHERE tariff_table_id = ? AND weight_from <= ? AND weight_to >= ?
            $table->index(['tariff_table_id', 'weight_from', 'weight_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_brackets');
    }
};
