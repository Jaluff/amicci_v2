<?php

use Illuminate\Support\Facades\DB;

echo "=== transport_routes origin/destination values ===\n";
$routes = DB::table('transport_routes')->get(['id', 'origin_id', 'destination_id', 'company_id', 'branch_id']);
foreach ($routes as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== dispatches origin/destination values ===\n";
$dispatches = DB::table('dispatches')->get(['id', 'origin_id', 'destination_id', 'company_id', 'branch_id']);
foreach ($dispatches as $d) {
    echo json_encode($d) . "\n";
}

echo "\n=== deliveries location_id values ===\n";
$deliveries = DB::table('deliveries')->get(['id', 'location_id', 'company_id', 'branch_id']);
foreach ($deliveries as $d) {
    echo json_encode($d) . "\n";
}

echo "\n=== ubicaciones with branch_id ===\n";
$ubis = DB::table('ubicaciones')->get();
foreach ($ubis as $u) {
    echo json_encode($u) . "\n";
}
