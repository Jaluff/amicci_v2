<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\TransportRoute;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BigDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar datos transaccionales previos
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Shipment::truncate();
        TransportRoute::truncate();
        Dispatch::truncate();
        Delivery::truncate();
        DB::table('shipment_items')->truncate();
        DB::table('status_histories')->truncate();
        DB::table('document_problems')->truncate();
        DB::table('activity_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $companies = Company::all();
        $ubicaciones = Ubicacion::all();
        // $drivers = Driver::all();
        // $deliverers = Deliverer::all();

        if ($companies->isEmpty() || $ubicaciones->isEmpty()) {
            $this->command->error('Debe haber empresas y ubicaciones para ejecutar este seeder.');
            return;
        }

        // 2. Crear 20 Clientes
        $this->command->info('Creando 20 clientes...');
        foreach ($companies as $company) {
            Party::factory()->count(10)->create([
                'company_id' => $company->id
            ]);
        }

        $allParties = Party::all();
        $allBranches = Branch::all();

        // 3. Crear 5 Guías (Shipments)
        // $this->command->info('Creando 5 guías...');
        // for ($i = 0; $i < 5; $i++) {
        //     $company = $companies->random();
        //     $branches = Branch::where('company_id', $company->id)->get();
        //     $branch = $branches->random();
            
        //     $origen = $ubicaciones->random();
        //     $destino = $ubicaciones->where('id', '!=', $origen->id)->random() ?? $ubicaciones->random();
            
        //     $remitente = $allParties->where('company_id', $company->id)->random();
        //     $destinatario = $allParties->where('company_id', $company->id)->where('id', '!=', $remitente->id)->random() ?? $remitente;

        //     $flete = rand(500, 8000);
        //     $total = $flete * 1.21;

        //     $shipment = Shipment::create([
        //         'company_id' => $company->id,
        //         'branch_id' => $branch->id,
        //         'numero' => $company->prefix . '-'. $branch->code .'-'.str_pad((string)($i + 1), 8, '0', STR_PAD_LEFT),
        //         'fecha' => now()->subDays(rand(0, 30))->format('Y-m-d'),
        //         'origen_id' => $origen->id,
        //         'destino_id' => $destino->id,
        //         'remitente_id' => $remitente->id,
        //         'destinatario_id' => $destinatario->id,
        //         'ubicacion_actual' => 'Dto origen',
        //         'flete' => $flete,
        //         'total' => $total,
        //         'subtotal' => $flete,
        //         'iva_monto' => $total - $flete,
        //     ]);

        //     DB::table('shipment_items')->insert([
        //         'shipment_id' => $shipment->id,
        //         'cantidad' => rand(1, 10),
        //         'tipo_paquete' => 'bultos',
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

        $availableShipments = Shipment::all();

        // 4. Crear 2 Rutas
        /* $this->command->info('Creando 2 rutas...');
        for ($i = 0; $i < 2; $i++) {
            $company = $companies->random();
            $branch = Branch::where('company_id', $company->id)->get()->random();
            $origen = $ubicaciones->random();
            $destino = $ubicaciones->where('id', '!=', $origen->id)->random() ?? $ubicaciones->random();

            $route = TransportRoute::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'route_number' => $company->prefix . '-R' . str_pad((string)($i + 1), 8, '0', STR_PAD_LEFT),
                'origin_id' => $origen->id,
                'destination_id' => $destino->id,
                'status' => 'Cargada'
            ]);

            $guíasParaRuta = $availableShipments->where('company_id', $company->id)
                ->where('branch_id', $branch->id) // Misma sucursal
                ->where('origen_id', $origen->id)
                ->where('destino_id', $destino->id)
                ->whereNull('transport_route_id')
                ->take(rand(1, 3));

            foreach ($guíasParaRuta as $s) {
                $s->update(['transport_route_id' => $route->id, 'ubicacion_actual' => 'En transito']);
            }
        } */

        // 5. Crear 3 Despachos
      /*   $this->command->info('Creando 3 despachos...');
        $availableRoutes = TransportRoute::all();
        for ($i = 0; $i < 3; $i++) {
            $company = $companies->random();
            $branch = Branch::where('company_id', $company->id)->get()->random();
            $driver = $drivers->random();
            $origen = $ubicaciones->random();
            $destino = $ubicaciones->where('id', '!=', $origen->id)->random() ?? $ubicaciones->random();

            $dispatch = Dispatch::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'dispatch_number' => $company->prefix . '-D' . str_pad((string)($i + 1), 8, '0', STR_PAD_LEFT),
                'origin_id' => $origen->id,
                'destination_id' => $destino->id,
                'driver_id' => $driver->id,
                'status' => 'Cargado',
                'cost' => rand(10000, 50000)
            ]);

            $rutasParaDespacho = $availableRoutes->where('company_id', $company->id)
                ->where('branch_id', $branch->id)
                ->where('origin_id', $origen->id)
                ->where('destination_id', $destino->id)
                ->whereNull('dispatch_id')
                ->take(rand(2, 5));

            foreach ($rutasParaDespacho as $r) {
                $r->update(['dispatch_id' => $dispatch->id]);
            }
        } */

        // 6. Crear 5 Repartos
        /* $this->command->info('Creando 5 repartos...');
        for ($i = 0; $i < 5; $i++) {
            $company = $companies->random();
            $branch = Branch::where('company_id', $company->id)->get()->random();
            $deliverer = $deliverers->random();
            $location = $ubicaciones->random();

            $delivery = Delivery::create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'delivery_number' => $company->prefix . '-REP' . str_pad((string)($i + 1), 8, '0', STR_PAD_LEFT),
                'deliverer_id' => $deliverer->id,
                'location_id' => $location->id,
                'status' => 'Listo',
                'load_date' => now()->format('Y-m-d')
            ]);

            $guíasParaReparto = Shipment::where('company_id', $company->id)
                ->where('branch_id', $branch->id)
                ->where('destino_id', $location->id)
                ->whereNull('transport_route_id')
                ->whereNull('delivery_id')
                ->take(rand(3, 8))
                ->get();

            foreach ($guíasParaReparto as $s) {
                $s->update(['delivery_id' => $delivery->id, 'ubicacion_actual' => 'En reparto']);
            }
            
            $delivery->update([
                'guide_count' => $guíasParaReparto->count(),
                'package_count' => $guíasParaReparto->count() * 2 
            ]);
        } */
        
        $this->command->info('Seeder finalizado con éxito.');
    }
}
