<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indexes = Illuminate\Support\Facades\DB::select('SHOW INDEX FROM dispatches');
print_r($indexes);
