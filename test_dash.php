<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

auth()->login(\App\Models\User::find(2) ?? \App\Models\User::first()); 
$req = \Illuminate\Http\Request::create('/api/stats', 'GET');
$res = app(\App\Http\Controllers\DashboardController::class)->stats($req);
$data = json_decode($res->getContent(), true);
echo json_encode([
    'kpi' => $data['kpi']['guias_con_problemas'],
    'problems' => $data['problem_list']
], JSON_PRETTY_PRINT);
