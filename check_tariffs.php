<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tariffs = App\Models\TariffTable::get(['id', 'name', 'origin_id', 'destination_id', 'is_active', 'rate_per_ton', 'rate_per_m3']);
echo "Tariff tables:\n";
foreach ($tariffs as $t) {
    echo json_encode($t) . "\n";
}

$ubicaciones = App\Models\Ubicacion::get(['id', 'nombre', 'branch_id']);
echo "\nUbicaciones:\n";
foreach ($ubicaciones as $u) {
    echo json_encode($u) . "\n";
}
