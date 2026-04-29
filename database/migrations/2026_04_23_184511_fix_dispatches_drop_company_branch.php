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
        // Drop unique constraint which is preventing column drop
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dispatches` DROP INDEX `1`");
        } catch (\Exception $e) { }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dispatches` DROP INDEX `dispatches_company_id_dispatch_number_unique`");
        } catch (\Exception $e) { }

        // Drop foreign keys if they exist
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dispatches` DROP FOREIGN KEY `1`");
        } catch (\Exception $e) { }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dispatches` DROP FOREIGN KEY `dispatches_company_id_foreign`");
        } catch (\Exception $e) { }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dispatches` DROP FOREIGN KEY `dispatches_branch_id_foreign`");
        } catch (\Exception $e) { }

        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            //
        });
    }
};
