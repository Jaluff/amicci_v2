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
            $table->dropColumn('origin');
            $table->dropColumn('destination');
        });
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->foreignId('origin_id')->after('route_number')->constrained('ubicaciones')->restrictOnDelete();
            $table->foreignId('destination_id')->after('origin_id')->constrained('ubicaciones')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origin_id');
            $table->dropConstrainedForeignId('destination_id');

            $table->string('origin')->after('route_number');
            $table->string('destination')->after('origin');
        });
    }
};