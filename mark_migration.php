<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$migrationName = '2026_04_23_000001_refactor_branches_ubicaciones_architecture';
$exists = DB::table('migrations')->where('migration', $migrationName)->exists();

if (! $exists) {
    DB::table('migrations')->insert([
        'migration' => $migrationName,
        'batch' => DB::table('migrations')->max('batch') + 1,
    ]);
    echo "Migration marked as done.\n";
} else {
    echo "Migration already marked.\n";
}
