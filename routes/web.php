<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TransportRouteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');



    // Gestión de Empresas
    Route::middleware('role:admin')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('company.update');
    });
});

// Rutas que requieren empresa activa (antes sesión, ahora stateless)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class , 'stats'])->name('dashboard.stats');

    Route::get('/shipments', [ShipmentController::class , 'index'])->name('shipments.index');
    Route::get('/shipments/datatable', [ShipmentController::class , 'datatable'])->name('shipments.datatable');
    Route::get('/shipments/create', [ShipmentController::class , 'create'])->name('shipments.create');
    Route::get('/shipments/calcular-flete', [ShipmentController::class, 'calcularFlete'])->name('shipments.calcular-flete');
    Route::post('/shipments', [ShipmentController::class , 'store'])->name('shipments.store');

    Route::get('/shipments/{shipment}/edit', [ShipmentController::class , 'edit'])->name('shipments.edit');
    Route::get('/shipments/{shipment}/print', [ShipmentController::class , 'print'])->name('shipments.print');
    Route::post('/shipments/print-massive', [ShipmentController::class, 'printMassive'])->name('shipments.print-massive');
    Route::post('/shipments/{shipment}', [ShipmentController::class , 'update'])->name('shipments.update');
    Route::delete('/shipments/{shipment}', [ShipmentController::class , 'destroy'])->name('shipments.destroy');

    // Clientes (Remitentes y Destinatarios)
    Route::post('/parties/ajax-store', [\App\Http\Controllers\PartyController::class , 'ajaxStore'])->name('parties.ajax-store');
    Route::get('/parties/datatable', [\App\Http\Controllers\PartyController::class , 'datatable'])->name('parties.datatable');
    Route::get('/parties/{party}/tariff-setting', [\App\Http\Controllers\PartyController::class , 'tariffSetting'])->name('parties.tariff-setting');
    Route::resource('parties', \App\Http\Controllers\PartyController::class)->except(['show']);


    // Conductores
    Route::get('/drivers/datatable', [\App\Http\Controllers\DriverController::class, 'datatable'])->name('drivers.datatable');
    Route::resource('drivers', \App\Http\Controllers\DriverController::class)->except(['show']);

    // Sucursales (Solo admin/supervisor)
    Route::middleware('role:admin|supervisor')->group(function () {
        Route::get('/branches/datatable', [BranchController::class, 'datatable'])->name('branches.datatable');
        Route::resource('branches', BranchController::class)->except(['show']);
    });

    // Repartidores
    Route::get('/deliverers/datatable', [\App\Http\Controllers\DelivererController::class, 'datatable'])->name('deliverers.datatable');
    Route::resource('deliverers', \App\Http\Controllers\DelivererController::class)->except(['show']);

    Route::get('/routes', [TransportRouteController::class , 'index'])->name('routes.index');
    Route::get('/routes/datatable', [TransportRouteController::class , 'datatable'])->name('routes.datatable');
    Route::get('/routes/available-shipments', [TransportRouteController::class , 'availableShipments'])->name('routes.available-shipments');
    Route::get('/routes/create', [TransportRouteController::class , 'create'])->name('routes.create');
    Route::post('/routes', [TransportRouteController::class , 'store'])->name('routes.store');
    Route::get('/routes/{route}/edit', [TransportRouteController::class , 'edit'])->name('routes.edit');
    Route::get('/routes/{route}/shipments', [TransportRouteController::class, 'getShipments'])->name('routes.shipments');
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
    Route::post('/deliveries/{delivery}/return-shipment/{shipment}', [\App\Http\Controllers\DeliveryController::class , 'returnShipment'])->name('deliveries.return-shipment');

    // Cargas (Full Truckloads)
    Route::get('/loads', [\App\Http\Controllers\LoadController::class, 'index'])->name('loads.index');
    Route::get('/loads/datatable', [\App\Http\Controllers\LoadController::class, 'datatable'])->name('loads.datatable');
    Route::get('/loads/create', [\App\Http\Controllers\LoadController::class, 'create'])->name('loads.create');
    Route::post('/loads', [\App\Http\Controllers\LoadController::class, 'store'])->name('loads.store');
    Route::get('/loads/{load}/edit', [\App\Http\Controllers\LoadController::class, 'edit'])->name('loads.edit');
    Route::put('/loads/{load}', [\App\Http\Controllers\LoadController::class, 'update'])->name('loads.update');
    Route::delete('/loads/{load}', [\App\Http\Controllers\LoadController::class, 'destroy'])->name('loads.destroy');
    Route::post('/loads/{load}/invoice', [\App\Http\Controllers\LoadController::class, 'invoice'])->name('loads.invoice');
    Route::post('/loads/{load}/pay', [\App\Http\Controllers\LoadController::class, 'pay'])->name('loads.pay');
    Route::post('/loads/{load}/change-state', [\App\Http\Controllers\LoadController::class, 'changeState'])->name('loads.change-state');

    // State Machine — transiciones de estado (aplica a todos los documentos logísticos)
    Route::post('/status/transition', [\App\Http\Controllers\StatusTransitionController::class , 'transition'])->name('status.transition');
    Route::get('/status/available', [\App\Http\Controllers\StatusTransitionController::class , 'available'])->name('status.available');

    // Problemas de documentos — historial polimórfico
    Route::post('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class , 'store'])->name('documents.problem.store');
    Route::get('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class , 'history'])->name('documents.problem.history');
    Route::get('/documents/problem/shipments', [\App\Http\Controllers\DocumentProblemController::class , 'shipmentProblems'])->name('documents.problem.shipments');

    // Cuadros Tarifarios — ABM con gestión de tramos de peso
    Route::get('/tariff-tables/datatable', [\App\Http\Controllers\TariffTableController::class, 'datatable'])->name('tariff-tables.datatable');
    Route::resource('tariff-tables', \App\Http\Controllers\TariffTableController::class)->except(['show']);
    // Facturación
    Route::get('/billing', [InvoiceController::class, 'index'])->name('billing.index');
    Route::get('/billing/datatable', [InvoiceController::class, 'datatable'])->name('billing.datatable');
    Route::get('/billing/invoices', [InvoiceController::class, 'invoicesIndex'])->name('billing.invoices');
    Route::get('/billing/invoices/datatable', [InvoiceController::class, 'invoicesDatatable'])->name('billing.invoices-datatable');
    Route::get('/billing/create', [InvoiceController::class, 'create'])->name('billing.create');
    Route::get('/billing/available-shipments', [InvoiceController::class, 'availableShipments'])->name('billing.available-shipments');
    Route::post('/billing', [InvoiceController::class, 'store'])->name('billing.store');
    Route::get('/billing/{invoice}', [InvoiceController::class, 'show'])->name('billing.show');
    Route::post('/billing/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])->name('billing.pay');

    // Edición de facturas — solo admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/billing/{invoice}/edit', [InvoiceController::class, 'edit'])->name('billing.edit');
        Route::put('/billing/{invoice}', [InvoiceController::class, 'update'])->name('billing.update');
    });
});


// Rutas exclusivas para administrador y supervisor
Route::middleware(['auth', 'role:admin|supervisor'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);


    // Reportes
    Route::get('/reports/dispatches', [\App\Http\Controllers\DispatchReportController::class, 'index'])->name('reports.dispatches.index');
    Route::get('/reports/dispatches/datatable', [\App\Http\Controllers\DispatchReportController::class, 'datatable'])->name('reports.dispatches.datatable');

    // Ubicaciones
    Route::resource('ubicaciones', \App\Http\Controllers\UbicacionController::class)->except(['show', 'create', 'edit']);
});

require __DIR__ . '/auth.php';