<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TransportRouteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Debug route
Route::get('/debug-parties', function () {
    $parties = \App\Models\Party::withoutGlobalScopes()->get();
    return response()->json([
    'session_company_id' => session('company_id'),
    'party_count' => $parties->count(),
    'parties' => $parties->pluck(['id', 'company_id', 'name'])->toArray()
    ]);
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'index'])
    ->middleware(['auth', 'verified', 'company'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    // Company selector & switch (sin middleware 'company' para evitar loop)
    Route::get('/company/select', [CompanyController::class , 'select'])->name('company.select');
    Route::post('/company/switch', [CompanyController::class , 'switch'])->name('company.switch');
});

// Rutas que requieren empresa activa en sesión
Route::middleware(['auth', 'company'])->group(function () {
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class , 'stats'])->name('dashboard.stats');

    Route::get('/shipments', [ShipmentController::class , 'index'])->name('shipments.index');
    Route::get('/shipments/datatable', [ShipmentController::class , 'datatable'])->name('shipments.datatable');
    Route::get('/shipments/create', [ShipmentController::class , 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class , 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}/edit', [ShipmentController::class , 'edit'])->name('shipments.edit');
    Route::post('/shipments/{shipment}', [ShipmentController::class , 'update'])->name('shipments.update');
    Route::delete('/shipments/{shipment}', [ShipmentController::class , 'destroy'])->name('shipments.destroy');

    Route::get('/routes', [TransportRouteController::class , 'index'])->name('routes.index');
    Route::get('/routes/datatable', [TransportRouteController::class , 'datatable'])->name('routes.datatable');
    Route::get('/routes/available-shipments', [TransportRouteController::class , 'availableShipments'])->name('routes.available-shipments');
    Route::get('/routes/create', [TransportRouteController::class , 'create'])->name('routes.create');
    Route::post('/routes', [TransportRouteController::class , 'store'])->name('routes.store');
    Route::get('/routes/{route}/edit', [TransportRouteController::class , 'edit'])->name('routes.edit');
    Route::put('/routes/{route}', [TransportRouteController::class , 'update'])->name('routes.update');
    Route::delete('/routes/{route}', [TransportRouteController::class , 'destroy'])->name('routes.destroy');

    Route::get('/dispatches', [\App\Http\Controllers\DispatchController::class , 'index'])->name('dispatches.index');
    Route::get('/dispatches/datatable', [\App\Http\Controllers\DispatchController::class , 'datatable'])->name('dispatches.datatable');
    Route::get('/dispatches/available-routes', [\App\Http\Controllers\DispatchController::class , 'availableRoutes'])->name('dispatches.available-routes');
    Route::get('/dispatches/create', [\App\Http\Controllers\DispatchController::class , 'create'])->name('dispatches.create');
    Route::post('/dispatches', [\App\Http\Controllers\DispatchController::class , 'store'])->name('dispatches.store');
    Route::get('/dispatches/{dispatch}/edit', [\App\Http\Controllers\DispatchController::class , 'edit'])->name('dispatches.edit');
    Route::put('/dispatches/{dispatch}', [\App\Http\Controllers\DispatchController::class , 'update'])->name('dispatches.update');
    Route::delete('/dispatches/{dispatch}', [\App\Http\Controllers\DispatchController::class , 'destroy'])->name('dispatches.destroy');

    Route::get('/deliveries', [\App\Http\Controllers\DeliveryController::class , 'index'])->name('deliveries.index');
    Route::get('/deliveries/datatable', [\App\Http\Controllers\DeliveryController::class , 'datatable'])->name('deliveries.datatable');
    Route::get('/deliveries/available-shipments', [\App\Http\Controllers\DeliveryController::class , 'availableShipments'])->name('deliveries.available-shipments');
    Route::get('/deliveries/create', [\App\Http\Controllers\DeliveryController::class , 'create'])->name('deliveries.create');
    Route::post('/deliveries', [\App\Http\Controllers\DeliveryController::class , 'store'])->name('deliveries.store');
    Route::get('/deliveries/{delivery}/edit', [\App\Http\Controllers\DeliveryController::class , 'edit'])->name('deliveries.edit');
    Route::put('/deliveries/{delivery}', [\App\Http\Controllers\DeliveryController::class , 'update'])->name('deliveries.update');
    Route::delete('/deliveries/{delivery}', [\App\Http\Controllers\DeliveryController::class , 'destroy'])->name('deliveries.destroy');

    // State Machine — transiciones de estado (aplica a todos los documentos logísticos)
    Route::post('/status/transition', [\App\Http\Controllers\StatusTransitionController::class , 'transition'])->name('status.transition');
    Route::get('/status/available', [\App\Http\Controllers\StatusTransitionController::class , 'available'])->name('status.available');

    // Problemas de documentos — historial polimórfico
    Route::post('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class , 'store'])->name('documents.problem.store');
    Route::get('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class , 'history'])->name('documents.problem.history');
    Route::get('/documents/problem/shipments', [\App\Http\Controllers\DocumentProblemController::class , 'shipmentProblems'])->name('documents.problem.shipments');
});


// Rutas exclusivas para administradores
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
});

require __DIR__ . '/auth.php';