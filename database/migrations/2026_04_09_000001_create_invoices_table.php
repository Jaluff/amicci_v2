<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // El cliente al que se le emite la factura
            $table->foreignId('party_id')->constrained('parties')->restrictOnDelete();

            // Datos de la factura (ingresados por el operador)
            $table->string('numero');
            $table->date('fecha_factura');
            $table->string('numero_recibo')->nullable();

            // Total desnormalizado (suma de totales de guías asociadas)
            $table->decimal('total', 12, 2)->default(0);

            // Estado
            $table->boolean('cobrada')->default(false);
            $table->date('fecha_cobro')->nullable();

            $table->text('notas')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // El número de factura es único por empresa
            $table->unique(['company_id', 'numero']);
            $table->index('party_id');
            $table->index('cobrada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
