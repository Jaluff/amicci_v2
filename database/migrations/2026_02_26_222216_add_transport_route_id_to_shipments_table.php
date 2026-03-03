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
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'transport_route_id')) {
                $table->foreignId('transport_route_id')->nullable()->constrained('transport_routes')->nullOnDelete();
                $table->index('transport_route_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'transport_route_id')) {
                $table->dropForeign(['transport_route_id']);
                $table->dropIndex(['transport_route_id']);
                $table->dropColumn('transport_route_id');
            }
        });
    }
};