<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica: funciona para Shipment, TransportRoute, Dispatch, Reparto, etc.
            $table->morphs('model'); // model_type + model_id + index

            $table->string('from_status')->nullable(); // null = creación inicial
            $table->string('to_status');
            $table->text('comment')->nullable();

            // Quién hizo el cambio
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('transitioned_at')->useCurrent();
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'transitioned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};