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
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_paquete', ['bultos', 'palets', 'sobres']);
            $table->smallInteger('cantidad')->default(1);
            $table->string('numero_remito')->nullable();
            $table->integer('peso', 10, 2)->default(0);
            $table->integer('volumen', 10, 3)->default(0);
            $table->decimal('monto_valor_declarado', 12, 2)->default(0);
            $table->decimal('monto_seguro_item', 12, 2)->default(0);
            $table->string('referencia_recepcion')->nullable();
            $table->string('referencia_orden_carga')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};