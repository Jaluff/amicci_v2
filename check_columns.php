<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "TransportRoutes columns:\n";
print_r(Schema::getColumnListing('transport_routes'));

echo "\nDispatches columns:\n";
print_r(Schema::getColumnListing('dispatches'));

echo "\nDeliveries columns:\n";
print_r(Schema::getColumnListing('deliveries'));
