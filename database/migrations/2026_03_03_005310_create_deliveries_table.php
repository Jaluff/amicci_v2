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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('delivery_number')->unique()->index();
            $table->foreignId('deliverer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->integer('guide_count')->default(0);
            $table->integer('package_count')->default(0);
            $table->date('load_date')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->enum('status', ['Listo', 'En reparto', 'Finalizado'])->default('Listo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};