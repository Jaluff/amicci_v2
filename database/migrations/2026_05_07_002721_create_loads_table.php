<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Añadir columna a companies para numeración secuencial
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('last_load_number')->default(0)->after('last_dispatch_number');
        });

        // Crear tabla de cargas
        Schema::create('loads', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Numeración
            $table->string('numero'); // Ej: PREFIX-000001
            $table->unique(['company_id', 'numero']);

            // Partes (Clientes)
            $table->foreignId('remitente_id')->constrained('parties')->restrictOnDelete();
            $table->foreignId('destinatario_id')->constrained('parties')->restrictOnDelete();

            // Ubicaciones (Sucursales)
            $table->foreignId('origen_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('destino_id')->constrained('branches')->restrictOnDelete();

            // Chofer
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();

            // Fechas
            $table->date('fecha_carga');
            $table->date('fecha_descarga')->nullable();

            // Otros datos
            $table->string('remito')->nullable();
            $table->text('observaciones')->nullable();

            // Estado (En origen, En viaje, En destino)
            $table->string('estado')->default('En origen');

            // Datos de Facturación
            $table->date('fecha_factura')->nullable();
            $table->string('numero_factura')->nullable();
            $table->decimal('importe_factura', 12, 2)->nullable();

            // Datos de Cobro
            $table->date('fecha_recibo')->nullable();
            $table->string('numero_recibo')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loads');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('last_load_number');
        });
    }
};
