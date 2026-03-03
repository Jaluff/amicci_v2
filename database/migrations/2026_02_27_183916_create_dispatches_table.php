<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->index();
            $table->string('dispatch_number')->unique()->index();
            $table->foreignId('origin_id')->constrained('ubicaciones');
            $table->foreignId('destination_id')->constrained('ubicaciones');
            $table->foreignId('driver_id')->constrained();
            $table->string('status')->default('Cargado')->index();
            $table->string('seal_number')->nullable();
            $table->string('semi_number')->nullable();
            $table->string('chassis_number')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });

        // Agregar dispatch_id a transport_routes para la relacion
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->foreignId('dispatch_id')->nullable()->constrained('dispatches')->nullOnDelete()->after('company_id');
        });

        // Contador de despachos en companies
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('last_dispatch_number')->default(0)->after('last_route_number');
        });
    }

    public function down(): void
    {
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropForeign(['dispatch_id']);
            $table->dropColumn('dispatch_id');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('last_dispatch_number');
        });
        Schema::dropIfExists('dispatches');
    }
};