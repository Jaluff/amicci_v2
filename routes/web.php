<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::get('/shipments', [ShipmentController::class , 'index'])->name('shipments.index');
    Route::get('/shipments/datatable', [ShipmentController::class , 'datatable'])->name('shipments.datatable');
    Route::get('/shipments/create', [ShipmentController::class , 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class , 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}/edit', [ShipmentController::class , 'edit'])->name('shipments.edit');
    Route::post('/shipments/{shipment}', [ShipmentController::class , 'update'])->name('shipments.update');
    Route::delete('/shipments/{shipment}', [ShipmentController::class , 'destroy'])->name('shipments.destroy');
});

// Rutas exclusivas para administradores
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
});

require __DIR__ . '/auth.php';