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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('cuit')->nullable()->after('legal_name');
            $table->string('gross_income')->nullable()->after('cuit');
            $table->string('establishment')->nullable()->after('gross_income');
            $table->string('stamping_headquarters')->nullable()->after('establishment');
            $table->date('start_of_activities')->nullable()->after('stamping_headquarters');

            $table->string('address_line1')->nullable()->after('start_of_activities');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('phone')->nullable()->after('address_line2');
            $table->string('email')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'cuit',
                'gross_income',
                'establishment',
                'stamping_headquarters',
                'start_of_activities',
                'address_line1',
                'address_line2',
                'phone',
                'email'
            ]);
        });
    }
};