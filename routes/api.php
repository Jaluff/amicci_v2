<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeliveryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PWA Repartidores
|--------------------------------------------------------------------------
|
| Autenticación con Sanctum (token-based).
| Prefix: /api (automático por Laravel).
|
*/

// Públicas
Route::post('/login', [AuthController::class, 'login']);

// Protegidas con token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Repartos del repartidor
    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show']);
    Route::post('/deliveries/{delivery}/confirm', [DeliveryController::class, 'confirm']);
    Route::post('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class, 'store']);
    Route::get('/documents/problem', [\App\Http\Controllers\DocumentProblemController::class, 'history']);
});
