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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('address')->nullable();
            $table->string('locality')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('phone_secondary', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('document', 50)->nullable();
            $table->string('document_type', 20)->nullable(); // DNI, CUIT, CUIL, etc.
            $table->string('tax_status', 50)->nullable();   // Responsable Inscripto, Monotributo, Consumidor Final, etc.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
