<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\EntityAuthController;
use App\Http\Controllers\Api\EntityShipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PWA Repartidores & Portal Clientes
|--------------------------------------------------------------------------
|
| Autenticación con Sanctum (token-based).
| Prefix: /api (automático por Laravel).
|
*/

// Públicas — Repartidores
Route::post('/login', [AuthController::class, 'login']);

// Públicas — Entidades / Clientes
Route::post('/entidades/login', [EntityAuthController::class, 'login']);

// Protegidas con token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Repartidores
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);
    Route::post('/deliveries/{delivery}/confirm', [DeliveryController::class, 'confirm']);
    Route::post('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class, 'store']);
    Route::get('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class, 'history']);

    // Entidades / Clientes
    Route::post('/entidades/logout', [EntityAuthController::class, 'logout']);
    Route::get('/entidades/me', [EntityAuthController::class, 'me']);
    Route::get('/guias', [EntityShipmentController::class, 'index']);
    Route::post('/guias', [EntityShipmentController::class, 'index']);
});
