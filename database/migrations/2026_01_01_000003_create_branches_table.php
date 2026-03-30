<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->string('name');             // "Casa Central BA", "Sucursal Mendoza"
            $table->unsignedTinyInteger('code'); // 1, 2, 3...
            $table->unsignedBigInteger('last_shipment_number')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']); // código único por empresa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
