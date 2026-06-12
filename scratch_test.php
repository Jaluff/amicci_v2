<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Party;
use App\Models\Shipment;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Ubicacion;
use App\Models\Dispatch;
use App\Models\TransportRoute;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

Queue::fake();
// Mail::fake();
config([
    'mail.default' => 'smtp',
    'mail.mailers.smtp.host' => 'mailpit',
    'mail.mailers.smtp.port' => 1025,
    'mail.mailers.smtp.encryption' => null,
    'mail.mailers.smtp.username' => null,
    'mail.mailers.smtp.password' => null,
]);
DB::beginTransaction();

try {
    $company = Company::first() ?? Company::create(['name' => 'Manual Test', 'prefix' => 'MT', 'active' => true]);
    
    $branch1 = Branch::first() ?? Branch::create(['name' => 'Branch Origin', 'code' => 101, 'active' => true]);
    $branch2 = Branch::create(['name' => 'Branch Dest', 'code' => 202, 'active' => true]);
    
    $origin = Ubicacion::create(['nombre' => 'Dto origen', 'branch_id' => $branch1->id]);
    $destination = Ubicacion::create(['nombre' => 'Dto destino', 'branch_id' => $branch2->id]);

    // Client R (Remitente)
    $clientR = Party::create([
        'name' => 'Client Remitente',
        'email' => 'shared@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    // Client D (Destinatario)
    $clientD = Party::create([
        'name' => 'Client Destinatario',
        'email' => 'shared@example.com',
        'email_notifications' => ['en_transito'],
    ]);

    // Client X (No email or no notifications)
    $clientX = Party::create([
        'name' => 'Client X (No Notifications)',
        'email' => 'clientX@example.com',
        'email_notifications' => [],
    ]);

    $route = TransportRoute::create([
        'company_id' => $company->id,
        'origin_id' => $branch1->id,
        'destination_id' => $branch2->id,
        'route_number' => 'R-TEST-DUPLICATE',
        'status' => 'Cargada',
    ]);

    // Shipment 1: Rem = R, Dest = D (Shared)
    $shipment1 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientR->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'S-SHARED-1',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
        'transport_route_id' => $route->id,
    ]);

    // Shipment 2: Rem = X, Dest = D
    $shipment2 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientX->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'S-D-2',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
        'transport_route_id' => $route->id,
    ]);

    // Shipment 3: Rem = X, Dest = D
    $shipment3 = Shipment::create([
        'company_id' => $company->id,
        'remitente_id' => $clientX->id,
        'destinatario_id' => $clientD->id,
        'origen_id' => $origin->id,
        'destino_id' => $destination->id,
        'numero' => 'S-D-3',
        'fecha' => now(),
        'ubicacion_actual' => 'Dto origen',
        'transport_route_id' => $route->id,
    ]);

    $driver = Driver::first() ?? Driver::create(['name' => 'Test Driver', 'dni' => '12345678']);

    $dispatch = Dispatch::create([
        'dispatch_number' => 'D-TEST-DUPLICATE',
        'origin_id' => $branch1->id,
        'destination_id' => $branch2->id,
        'driver_id' => $driver->id,
        'status' => 'Cargado',
    ]);

    $route->update(['dispatch_id' => $dispatch->id]);

    echo "Transitioning dispatch to En viaje...\n";
    $dispatch->stateMachine()->transitionTo('En viaje', 'Testing duplicate issue');

    // Inspect queued SendGroupedShipmentsEmailJob instances
    $pushedJobs = Queue::pushed(\App\Jobs\SendGroupedShipmentsEmailJob::class);
    echo "\nQueued jobs count: " . count($pushedJobs) . "\n";
    
    foreach ($pushedJobs as $index => $job) {
        $emailLogIds = $job->emailLogIds;
        $logs = \App\Models\EmailLog::whereIn('id', $emailLogIds)->get();
        
        // Get the unique shipments and recipient
        $shipmentNums = $logs->map(fn($l) => $l->shipment->numero)->toArray();
        $recipient = $logs->first()->recipient;
        $partyName = $logs->first()->party->name;
        
        echo "\nExecuting Job #" . ($index + 1) . ":\n";
        echo "  Recipient Email: {$recipient}\n";
        echo "  Party (Client): {$partyName}\n";
        echo "  Shipments in Email: " . implode(', ', $shipmentNums) . "\n";
        echo "  Log IDs: " . implode(', ', $emailLogIds) . "\n";
        
        $job->handle();
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
}
