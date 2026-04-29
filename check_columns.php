<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TransportRoutes columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('transport_routes'));

echo "\nDispatches columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('dispatches'));

echo "\nDeliveries columns:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('deliveries'));
