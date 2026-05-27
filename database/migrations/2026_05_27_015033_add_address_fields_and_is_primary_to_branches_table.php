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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('address_line1')->nullable()->after('name');
            $table->string('city')->nullable()->after('address_line1');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->string('phone')->nullable()->after('zip_code');
            $table->string('email')->nullable()->after('phone');
            $table->boolean('is_primary')->default(false)->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['address_line1', 'city', 'state', 'zip_code', 'phone', 'email', 'is_primary']);
        });
    }
};
