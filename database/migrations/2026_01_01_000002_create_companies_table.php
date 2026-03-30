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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('cuit')->nullable();
            $table->string('prefix', 10); // Ej: A, B, EXP, etc
            $table->string('gross_income')->nullable();
            $table->string('establishment')->nullable();
            $table->string('stamping_headquarters')->nullable();
            $table->date('start_of_activities')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            $table->unsignedBigInteger('last_shipment_number')->default(0);
            $table->unsignedBigInteger('last_route_number')->default(0);
            $table->unsignedBigInteger('last_dispatch_number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};