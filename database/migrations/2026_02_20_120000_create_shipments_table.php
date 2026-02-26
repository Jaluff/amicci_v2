<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Identification
            $table->string('numero')->unique(); // prefijo + correlativo
            $table->date('fecha');

            // Locations (references to ubicaciones table)
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->foreign('origen_id')->references('id')->on('ubicaciones')->nullOnDelete();
            
            $table->unsignedBigInteger('destino_id')->nullable();
            $table->foreign('destino_id')->references('id')->on('ubicaciones')->nullOnDelete();

            // Parties (references to parties table)
            $table->unsignedBigInteger('remitente_id')->nullable();
            $table->foreign('remitente_id')->references('id')->on('parties')->nullOnDelete();
            
            $table->unsignedBigInteger('destinatario_id')->nullable();
            $table->foreign('destinatario_id')->references('id')->on('parties')->nullOnDelete();

            
            
            // Invoice & Delivery
            $table->string('numero_factura')->nullable();
            $table->enum('estado_facturacion', ['No facturada', 'Facturada', 'Rendida', 'Anulada'])->default('No facturada');
            $table->enum('ubicacion_actual', ['Dto origen', 'En transito', 'Dto destino', 'En reparto', 'Entregado'])->default('Dto origen');
            $table->enum('flete_a_pagar_en', ['Origen', 'Destino'])->nullable();
            $table->date('fecha_entrega')->nullable();
            
            $table->boolean('cobrada')->default(false);
            $table->boolean('contra_reembolso')->default(false);
            $table->boolean('rendida')->default(false);

            // Amounts
            $table->decimal('flete', 12, 2)->default(0);
            $table->decimal('seguro', 12, 2)->default(0);
            $table->decimal('monto_contra_reembolso', 12, 2)->default(0);
            $table->decimal('retencion_mercaderia', 12, 2)->default(0);
            $table->decimal('otros_cargos', 12, 2)->default(0);

            // Totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva_monto', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Notes
            $table->text('notas')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};