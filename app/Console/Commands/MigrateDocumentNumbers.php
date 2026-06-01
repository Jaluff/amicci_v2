<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Models\Dispatch;
use App\Models\Load;
use App\Models\Shipment;
use App\Models\TransportRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDocumentNumbers extends Command
{
    protected $signature = 'amicci:migrate-numbers';

    protected $description = 'Migrate old document numbers to the new format [PREFIX]-[BRANCH]-[LETRA]-[NUM]';

    public function handle()
    {
        DB::transaction(function () {
            // Helper para extraer sucursal
            $extractBranch = function ($part0, $defaultBranch) {
                if (preg_match('/^[A-Z]+(\d+)[A-Z]$/', $part0, $matches)) {
                    return (int)$matches[1];
                }
                return $defaultBranch;
            };

            // 1. Guías (Shipments)
            $this->info('Migrating Shipments...');
            $shipments = Shipment::with('company')->get();
            foreach ($shipments as $s) {
                $parts = explode('-', $s->numero);
                $num = (int) end($parts);
                $prefix = substr($s->company?->prefix ?? 'A', 0, 1);
                $branch = count($parts) === 4 ? (int)$parts[1] : (count($parts) === 2 ? $extractBranch($parts[0], $s->branch_id ?? 0) : ($s->branch_id ?? 0));
                $s->numero = sprintf('%s%dG-%06d', $prefix, $branch, $num);
                $s->save();
            }

            // 2. Rutas (TransportRoutes)
            $this->info('Migrating Routes...');
            $routes = TransportRoute::with(['company'])->get();
            foreach ($routes as $r) {
                $parts = explode('-', $r->route_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = substr($r->company?->prefix ?? 'A', 0, 1);
                $branch = count($parts) === 4 ? (int)$parts[1] : (count($parts) === 2 ? $extractBranch($parts[0], $r->origin_id ?? 0) : ($r->origin_id ?? 0));
                $r->route_number = sprintf('%s%dH-%06d', $prefix, $branch, $num);
                $r->save();
            }

            // 3. Despachos (Dispatches)
            $this->info('Migrating Dispatches...');
            $dispatches = Dispatch::all();
            foreach ($dispatches as $d) {
                $parts = explode('-', $d->dispatch_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $branch = count($parts) === 4 ? (int)$parts[1] : (count($parts) === 2 ? $extractBranch($parts[0], $d->origin_id ?? 0) : ($d->origin_id ?? 0));
                $d->dispatch_number = sprintf('A%dD-%06d', $branch, $num);
                $d->save();
            }

            // 4. Repartos (Deliveries)
            $this->info('Migrating Deliveries...');
            $deliveries = Delivery::with(['company'])->get();
            foreach ($deliveries as $d) {
                $parts = explode('-', $d->delivery_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = substr($d->company?->prefix ?? 'A', 0, 1);
                $branch = count($parts) === 4 ? (int)$parts[1] : (count($parts) === 2 ? $extractBranch($parts[0], $d->location_id ?? 0) : ($d->location_id ?? 0));
                $d->delivery_number = sprintf('%s%dR-%06d', $prefix, $branch, $num);
                $d->save();
            }

            // 5. Cargas (Loads)
            $this->info('Migrating Loads...');
            $loads = Load::with(['company'])->get();
            foreach ($loads as $l) {
                $parts = explode('-', $l->numero);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = substr($l->company?->prefix ?? 'A', 0, 1);
                $branch = count($parts) === 4 ? (int)$parts[1] : (count($parts) === 2 ? $extractBranch($parts[0], $l->origen_id ?? 0) : ($l->origen_id ?? 0));
                $l->numero = sprintf('%s%dC-%06d', $prefix, $branch, $num);
                $l->save();
            }
        });

        $this->info('All document numbers migrated successfully to the new format!');
    }
}
