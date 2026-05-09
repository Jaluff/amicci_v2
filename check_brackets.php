<?php

use App\Models\TariffBracket;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$brackets = TariffBracket::limit(10)->get();
echo 'Brackets count: '.TariffBracket::count()."\n";
foreach ($brackets as $b) {
    echo json_encode($b)."\n";
}
