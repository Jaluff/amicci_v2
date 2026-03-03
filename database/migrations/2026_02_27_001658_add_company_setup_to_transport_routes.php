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
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropUnique(['route_number']);
            $table->foreignId('company_id')->after('id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->unique(['company_id', 'route_number']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('last_route_number')->default(0)->after('last_shipment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('last_route_number');
        });

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'route_number']);
            $table->dropConstrainedForeignId('company_id');
            $table->unique('route_number');
        });
    }
};