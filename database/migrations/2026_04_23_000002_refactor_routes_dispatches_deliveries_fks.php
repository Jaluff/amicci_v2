<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── PASO 5: Routes - remap FKs de ubicaciones → branches ───

        // Helper to drop foreign key if it exists
        $dropFkIfExists = function (string $table, string $key) {
            try {
                Schema::table($table, function (Blueprint $t) use ($key) {
                    $t->dropForeign([$key]);
                });
            } catch (Exception $e) {
            } // Ignore if already dropped
        };

        $dropConstraintIfExists = function (string $table, string $name) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            } catch (Exception $e) {
            } // Ignore if already dropped
        };

        // DROP FKs on transport_routes
        $dropFkIfExists('transport_routes', 'origin_id');
        $dropFkIfExists('transport_routes', 'destination_id');
        $dropFkIfExists('transport_routes', 'company_id');
        $dropFkIfExists('transport_routes', 'branch_id');
        $dropFkIfExists('transport_routes', 'dispatch_id');

        // Drop the unique constraint if it exists
        try {
            Schema::table('transport_routes', function (Blueprint $table) {
                $table->dropUnique(['company_id', 'route_number']);
            });
        } catch (Exception $e) {
        }

        // Create route_number unique if not exists
        try {
            Schema::table('transport_routes', function (Blueprint $table) {
                $table->unique('route_number');
            });
        } catch (Exception $e) {
        }

        // Drop columns
        try {
            Schema::table('transport_routes', function (Blueprint $table) {
                $table->dropColumn(['company_id', 'branch_id']);
            });
        } catch (Exception $e) {
        }

        // Re-add FKs pointing to branches
        try {
            Schema::table('transport_routes', function (Blueprint $table) {
                $table->foreign('origin_id')->references('id')->on('branches')->restrictOnDelete();
                $table->foreign('destination_id')->references('id')->on('branches')->restrictOnDelete();
                $table->foreign('dispatch_id')->references('id')->on('dispatches')->nullOnDelete();
            });
        } catch (Exception $e) {
        }

        // ─── PASO 6: Dispatches ───
        $dropFkIfExists('dispatches', 'origin_id');
        $dropFkIfExists('dispatches', 'destination_id');
        $dropFkIfExists('dispatches', 'company_id');
        $dropFkIfExists('dispatches', 'branch_id');

        try {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->dropColumn(['company_id', 'branch_id']);
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->foreign('origin_id')->references('id')->on('branches')->restrictOnDelete();
                $table->foreign('destination_id')->references('id')->on('branches')->restrictOnDelete();
            });
        } catch (Exception $e) {
        }

        // ─── PASO 7: Deliveries ───
        $dropFkIfExists('deliveries', 'location_id');
        $dropFkIfExists('deliveries', 'company_id');
        $dropFkIfExists('deliveries', 'branch_id');

        try {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->dropColumn(['company_id', 'branch_id']);
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->foreign('location_id')->references('id')->on('branches')->restrictOnDelete();
            });
        } catch (Exception $e) {
        }
    }

    public function down(): void {}
};
