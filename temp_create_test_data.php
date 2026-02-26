<?php

use App\Models\Company;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = Company::create([
    'name' => 'Amicci Test',
    'prefix' => 'TEST',
    'last_shipment_number' => 0
]);

User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password')
])->companies()->attach($company->id);

echo "Test data created successfully.\n";
