<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDocumentLetters extends Command
{
    protected $signature = 'amicci:update-document-letters
                            {--dry-run : Muestra cuántos registros serán afectados sin modificar nada}';

    protected $description = 'Actualiza las letras identificadoras en la numeración de documentos: rutas (R→H) y repartos (E→R)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se modificará ningún registro.');
            $this->newLine();
        }

        // ---------------------------------------------------------------
        // 1. Rutas: xNR-NNNNNN → xNH-NNNNNN
        // ---------------------------------------------------------------
        $routes = DB::table('transport_routes')
            ->where('route_number', 'REGEXP', '[A-Z][0-9]+R-[0-9]{6}')
            ->select('id', 'route_number')
            ->get();

        $routesCount = $routes->count();
        $this->info("Rutas a actualizar (R → H): {$routesCount}");

        if ($routesCount > 0 && ! $dryRun) {
            DB::transaction(function () use ($routes) {
                foreach ($routes as $route) {
                    $newNumber = preg_replace('/([A-Z][0-9]+)R-([0-9]{6})/', '$1H-$2', $route->route_number);
                    DB::table('transport_routes')
                        ->where('id', $route->id)
                        ->update([
                            'route_number' => $newNumber,
                            'updated_at'   => now(),
                        ]);
                }
            });

            $this->info("✅ {$routesCount} rutas actualizadas.");
        }

        // ---------------------------------------------------------------
        // 2. Repartos: xNE-NNNNNN → xNR-NNNNNN
        // ---------------------------------------------------------------
        $deliveries = DB::table('deliveries')
            ->where('delivery_number', 'REGEXP', '[A-Z][0-9]+E-[0-9]{6}')
            ->select('id', 'delivery_number')
            ->get();

        $deliveriesCount = $deliveries->count();
        $this->info("Repartos a actualizar (E → R): {$deliveriesCount}");

        if ($deliveriesCount > 0 && ! $dryRun) {
            DB::transaction(function () use ($deliveries) {
                foreach ($deliveries as $delivery) {
                    $newNumber = preg_replace('/([A-Z][0-9]+)E-([0-9]{6})/', '$1R-$2', $delivery->delivery_number);
                    DB::table('deliveries')
                        ->where('id', $delivery->id)
                        ->update([
                            'delivery_number' => $newNumber,
                            'updated_at'      => now(),
                        ]);
                }
            });

            $this->info("✅ {$deliveriesCount} repartos actualizados.");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('Ejecutá el comando sin --dry-run para aplicar los cambios.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('¡Letras de documentos actualizadas correctamente!');

        return self::SUCCESS;
    }
}
