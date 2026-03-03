<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Modificar el ENUM directamente con SQL para agregar 'Con problemas'
        DB::statement("
            ALTER TABLE shipments
            MODIFY COLUMN ubicacion_actual
            ENUM('Dto origen', 'En transito', 'Dto destino', 'En reparto', 'Entregado', 'Con problemas')
            NOT NULL DEFAULT 'Dto origen'
        ");
    }

    public function down(): void
    {
        // Revertir: quitar 'Con problemas' del ENUM
        // Primero actualizar registros que puedan tener ese valor para no romper la constraint
        DB::statement("UPDATE shipments SET ubicacion_actual = 'Dto destino' WHERE ubicacion_actual = 'Con problemas'");

        DB::statement("
            ALTER TABLE shipments
            MODIFY COLUMN ubicacion_actual
            ENUM('Dto origen', 'En transito', 'Dto destino', 'En reparto', 'Entregado')
            NOT NULL DEFAULT 'Dto origen'
        ");
    }
};