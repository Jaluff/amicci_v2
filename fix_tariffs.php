<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t3 = App\Models\TariffTable::find(3);
if ($t3) {
    // Origen: Buenos Aires (Cap. Fed) -> Ubicacion id 5
    // Destino: Mendoza -> Ubicacion id 4
    $t3->origin_id = 5;
    $t3->destination_id = 4;
    $t3->save();
    echo "Tariff 3 fixed: {$t3->origin_id} -> {$t3->destination_id}\n";
} else {
    echo "Tariff 3 not found.\n";
}
