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
            // 1. Guías (Shipments)
            $this->info('Migrating Shipments...');
            $shipments = Shipment::with('company')->get();
            foreach ($shipments as $s) {
                if (preg_match('/-[A-Z]-/', $s->numero)) {
                    continue;
                } // Ya migrado

                $parts = explode('-', $s->numero);
                if (count($parts) >= 2) {
                    $num = (int) end($parts);
                    $prefix = $s->company?->prefix ?? 'AMI';
                    $branch = $s->branch_id ?? 0;
                    $s->numero = sprintf('%s-%d-G-%08d', $prefix, $branch, $num);
                    $s->save();
                }
            }

            // 2. Rutas (TransportRoutes)
            $this->info('Migrating Routes...');
            $routes = TransportRoute::with(['company'])->get();
            foreach ($routes as $r) {
                if (preg_match('/-[A-Z]-/', $r->route_number)) {
                    continue;
                }

                $parts = explode('-', $r->route_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = $r->company?->prefix ?? 'AMI';
                $branch = $r->origin_id ?? 0;
                $r->route_number = sprintf('%s-%d-R-%08d', $prefix, $branch, $num);
                $r->save();
            }

            // 3. Despachos (Dispatches)
            $this->info('Migrating Dispatches...');
            $dispatches = Dispatch::all();
            foreach ($dispatches as $d) {
                if (preg_match('/-[A-Z]-/', $d->dispatch_number)) {
                    continue;
                }

                $parts = explode('-', $d->dispatch_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $branch = $d->origin_id ?? 0;
                $d->dispatch_number = sprintf('AMI-%d-D-%08d', $branch, $num);
                $d->save();
            }

            // 4. Repartos (Deliveries)
            $this->info('Migrating Deliveries...');
            $deliveries = Delivery::with(['company'])->get();
            foreach ($deliveries as $d) {
                if (preg_match('/-[A-Z]-/', $d->delivery_number)) {
                    continue;
                }

                $parts = explode('-', $d->delivery_number);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = $d->company?->prefix ?? 'AMI';
                $branch = $d->location_id ?? 0;
                $d->delivery_number = sprintf('%s-%d-E-%08d', $prefix, $branch, $num);
                $d->save();
            }

            // 5. Cargas (Loads)
            $this->info('Migrating Loads...');
            $loads = Load::with(['company'])->get();
            foreach ($loads as $l) {
                if (preg_match('/-[A-Z]-/', $l->numero)) {
                    continue;
                }

                $parts = explode('-', $l->numero);
                $num = (int) filter_var(end($parts), FILTER_SANITIZE_NUMBER_INT);
                $prefix = $l->company?->prefix ?? 'AMI';
                $branch = $l->origen_id ?? 0;
                $l->numero = sprintf('%s-%d-C-%08d', $prefix, $branch, $num);
                $l->save();
            }
        });

        $this->info('All document numbers migrated successfully!');
    }
}
