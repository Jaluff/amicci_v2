<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add company_id to transport_routes
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies');
        });

        // Add company_id to dispatches
        Schema::table('dispatches', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies');
        });

        // Add company_id to deliveries
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies');
        });

        // Add color to companies
        Schema::table('companies', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->default('#6366f1')->after('prefix');
        });
    }

    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
