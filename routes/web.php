<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DelivererController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\DispatchReportController;
use App\Http\Controllers\DocumentProblemController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StatusTransitionController;
use App\Http\Controllers\TariffTableController;
use App\Http\Controllers\TransportRouteController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestión de Empresas
    Route::middleware('role:admin')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('company.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('company.update');
    });
});

// Rutas que requieren empresa activa (antes sesión, ahora stateless)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/datatable', [ShipmentController::class, 'datatable'])->name('shipments.datatable');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::get('/shipments/calcular-flete', [ShipmentController::class, 'calcularFlete'])->name('shipments.calcular-flete');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');

    Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
    Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
    Route::post('/shipments/print-massive', [ShipmentController::class, 'printMassive'])->name('shipments.print-massive');
    Route::post('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

    // Clientes (Remitentes y Destinatarios)
    Route::get('/parties/ajax-search', [PartyController::class, 'ajaxSearch'])->name('parties.ajax-search');
    Route::post('/parties/ajax-store', [PartyController::class, 'ajaxStore'])->name('parties.ajax-store');
    Route::post('/parties/{party}/ajax-address', [PartyController::class, 'ajaxStoreAddress'])->name('parties.ajax-address');
    Route::get('/parties/datatable', [PartyController::class, 'datatable'])->name('parties.datatable');
    Route::get('/parties/{party}/tariff-setting', [PartyController::class, 'tariffSetting'])->name('parties.tariff-setting');
    Route::resource('parties', PartyController::class)->except(['show']);

    // Conductores
    Route::get('/drivers/datatable', [DriverController::class, 'datatable'])->name('drivers.datatable');
    Route::resource('drivers', DriverController::class)->except(['show']);

    // Sucursales (Solo admin/supervisor)
    Route::middleware('role:admin|supervisor')->group(function () {
        Route::get('/branches/datatable', [BranchController::class, 'datatable'])->name('branches.datatable');
        Route::resource('branches', BranchController::class)->except(['show']);
    });

    // Repartidores
    Route::get('/deliverers/datatable', [DelivererController::class, 'datatable'])->name('deliverers.datatable');
    Route::resource('deliverers', DelivererController::class)->except(['show']);

    Route::get('/routes', [TransportRouteController::class, 'index'])->name('routes.index');
    Route::get('/routes/datatable', [TransportRouteController::class, 'datatable'])->name('routes.datatable');
    Route::get('/routes/available-shipments', [TransportRouteController::class, 'availableShipments'])->name('routes.available-shipments');
    Route::get('/routes/create', [TransportRouteController::class, 'create'])->name('routes.create');
    Route::post('/routes', [TransportRouteController::class, 'store'])->name('routes.store');
    Route::get('/routes/{route}/edit', [TransportRouteController::class, 'edit'])->name('routes.edit');
    Route::get('/routes/{route}/shipments', [TransportRouteController::class, 'getShipments'])->name('routes.shipments');
    Route::put('/routes/{route}', [TransportRouteController::class, 'update'])->name('routes.update');
    Route::delete('/routes/{route}', [TransportRouteController::class, 'destroy'])->name('routes.destroy');

    Route::get('/dispatches', [DispatchController::class, 'index'])->name('dispatches.index');
    Route::get('/dispatches/datatable', [DispatchController::class, 'datatable'])->name('dispatches.datatable');
    Route::get('/dispatches/available-routes', [DispatchController::class, 'availableRoutes'])->name('dispatches.available-routes');
    Route::get('/dispatches/create', [DispatchController::class, 'create'])->name('dispatches.create');
    Route::post('/dispatches', [DispatchController::class, 'store'])->name('dispatches.store');
    Route::get('/dispatches/{dispatch}/edit', [DispatchController::class, 'edit'])->name('dispatches.edit');
    Route::put('/dispatches/{dispatch}', [DispatchController::class, 'update'])->name('dispatches.update');
    Route::delete('/dispatches/{dispatch}', [DispatchController::class, 'destroy'])->name('dispatches.destroy');

    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/datatable', [DeliveryController::class, 'datatable'])->name('deliveries.datatable');
    Route::get('/deliveries/available-shipments', [DeliveryController::class, 'availableShipments'])->name('deliveries.available-shipments');
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('/deliveries/{delivery}/edit', [DeliveryController::class, 'edit'])->name('deliveries.edit');
    Route::get('/deliveries/{delivery}/print', [DeliveryController::class, 'print'])->name('deliveries.print');
    Route::put('/deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');
    Route::delete('/deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('deliveries.destroy');
    Route::post('/deliveries/{delivery}/return-shipment/{shipment}', [DeliveryController::class, 'returnShipment'])->name('deliveries.return-shipment');

    // Cargas (Full Truckloads)
    Route::get('/loads', [LoadController::class, 'index'])->name('loads.index');
    Route::get('/loads/datatable', [LoadController::class, 'datatable'])->name('loads.datatable');
    Route::get('/loads/create', [LoadController::class, 'create'])->name('loads.create');
    Route::post('/loads', [LoadController::class, 'store'])->name('loads.store');
    Route::get('/loads/{load}/edit', [LoadController::class, 'edit'])->name('loads.edit');
    Route::put('/loads/{load}', [LoadController::class, 'update'])->name('loads.update');
    Route::delete('/loads/{load}', [LoadController::class, 'destroy'])->name('loads.destroy');
    Route::post('/loads/{load}/invoice', [LoadController::class, 'invoice'])->name('loads.invoice');
    Route::post('/loads/{load}/pay', [LoadController::class, 'pay'])->name('loads.pay');
    Route::post('/loads/{load}/change-state', [LoadController::class, 'changeState'])->name('loads.change-state');

    // State Machine — transiciones de estado (aplica a todos los documentos logísticos)
    Route::post('/status/transition', [StatusTransitionController::class, 'transition'])->name('status.transition');
    Route::get('/status/available', [StatusTransitionController::class, 'available'])->name('status.available');

    // Problemas de documentos — historial polimórfico
    Route::post('/documents/problem', [DocumentProblemController::class, 'store'])->name('documents.problem.store');
    Route::get('/documents/problem', [DocumentProblemController::class, 'history'])->name('documents.problem.history');
    Route::get('/documents/problem/shipments', [DocumentProblemController::class, 'shipmentProblems'])->name('documents.problem.shipments');

    // Cuadros Tarifarios — ABM con gestión de tramos de peso
    Route::get('/tariff-tables/datatable', [TariffTableController::class, 'datatable'])->name('tariff-tables.datatable');
    Route::resource('tariff-tables', TariffTableController::class)->except(['show']);
    // Facturación
    Route::get('/billing', [InvoiceController::class, 'index'])->name('billing.index');
    Route::get('/billing/datatable', [InvoiceController::class, 'datatable'])->name('billing.datatable');
    Route::get('/billing/invoices', [InvoiceController::class, 'invoicesIndex'])->name('billing.invoices');
    Route::get('/billing/invoices/datatable', [InvoiceController::class, 'invoicesDatatable'])->name('billing.invoices-datatable');
    Route::get('/billing/create', [InvoiceController::class, 'create'])->name('billing.create');
    Route::get('/billing/available-shipments', [InvoiceController::class, 'availableShipments'])->name('billing.available-shipments');
    Route::post('/billing', [InvoiceController::class, 'store'])->name('billing.store');
    Route::get('/billing/{invoice}', [InvoiceController::class, 'show'])->name('billing.show');
    Route::get('/billing/{invoice}/print', [InvoiceController::class, 'print'])->name('billing.print');
    Route::get('/billing/{invoice}/excel', [InvoiceController::class, 'excel'])->name('billing.excel');
    Route::post('/billing/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])->name('billing.pay');

    // Edición de facturas — solo admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/billing/{invoice}/edit', [InvoiceController::class, 'edit'])->name('billing.edit');
        Route::put('/billing/{invoice}', [InvoiceController::class, 'update'])->name('billing.update');
    });
});

// Rutas exclusivas para administrador y supervisor
Route::middleware(['auth', 'role:admin|supervisor'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);

    // Reportes
    Route::get('/reports/dispatches', [DispatchReportController::class, 'index'])->name('reports.dispatches.index');
    Route::get('/reports/dispatches/datatable', [DispatchReportController::class, 'datatable'])->name('reports.dispatches.datatable');

    // Ubicaciones
    Route::resource('ubicaciones', UbicacionController::class)->except(['show', 'create', 'edit']);
});

require __DIR__.'/auth.php';
