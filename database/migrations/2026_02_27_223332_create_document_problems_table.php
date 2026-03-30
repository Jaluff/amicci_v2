<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_problems', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica: Shipment, TransportRoute, Dispatch, Reparto, etc.
            $table->morphs('documentable'); // documentable_type + documentable_id + index

            // Cada registro es un evento inmutable del historial de problemas
            $table->boolean('is_active')->default(true); // true = abierto, false = resuelto
            $table->text('comment'); // descripción del problema o su resolución

            // Quién lo registró
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id', 'created_at'], 'doc_problems_poly_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_problems');
    }
};