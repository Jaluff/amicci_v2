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
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_route_id')->nullable()->constrained('transport_routes')->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();

            // Identification
            $table->string('numero')->unique();
            $table->date('fecha');

            // Locations (references to ubicaciones table)
            $table->foreignId('origen_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('destino_id')->nullable()->constrained('ubicaciones')->nullOnDelete();

            // Parties (references to parties table)
            $table->foreignId('remitente_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('destinatario_id')->nullable()->constrained('parties')->nullOnDelete();

            // Invoice & Delivery
            $table->string('numero_factura')->nullable();
            $table->enum('ubicacion_actual', ['Dto origen', 'En transito', 'Dto destino', 'En reparto', 'Entregado', 'Con problemas'])->default('Dto origen');
            $table->enum('flete_a_pagar_en', ['Origen', 'Destino'])->nullable();
            $table->date('fecha_entrega')->nullable();
            
            $table->boolean('cobrada')->default(false);
            $table->boolean('contra_reembolso')->default(false);

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

            $table->index('transport_route_id');
            $table->index('delivery_id');
            $table->index('branch_id');
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