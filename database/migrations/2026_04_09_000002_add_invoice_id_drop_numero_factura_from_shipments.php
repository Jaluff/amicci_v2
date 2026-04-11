<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Eliminamos el campo de texto libre (reemplazado por la relación)
            $table->dropColumn('numero_factura');

            // FK a la nueva tabla de facturas
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('delivery_id')
                ->constrained('invoices')
                ->nullOnDelete();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
            $table->string('numero_factura')->nullable();
        });
    }
};
