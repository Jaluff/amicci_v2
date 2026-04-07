<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->decimal('iva_percent', 5, 2)->nullable()->default(0)->after('tax_status');
            $table->boolean('has_insurance')->default(false)->after('iva_percent');
            $table->decimal('insurance_percent', 5, 2)->nullable()->after('has_insurance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['iva_percent', 'has_insurance', 'insurance_percent']);
        });
    }
};
