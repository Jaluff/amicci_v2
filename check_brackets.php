<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$brackets = App\Models\TariffBracket::limit(10)->get();
echo "Brackets count: " . App\Models\TariffBracket::count() . "\n";
foreach ($brackets as $b) {
    echo json_encode($b) . "\n";
}
