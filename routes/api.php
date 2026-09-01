<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiNetworkStatusController;
use App\Http\Controllers\Api\ApiKeluhanController;
use App\Http\Controllers\Api\ApiPaymentController;
use App\Http\Middleware\ApiAuthMiddleware;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware([ApiAuthMiddleware::class])->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    
    Route::get('/dashboard', [ApiDashboardController::class, 'index']);
    Route::get('/dashboard/router-stats', [ApiDashboardController::class, 'routerStats']);
    
    Route::get('/jaringan/status', [ApiNetworkStatusController::class, 'index']);
    Route::post('/jaringan/status/wifi', [ApiNetworkStatusController::class, 'updateWifi']);
    
    Route::get('/laporan', [ApiKeluhanController::class, 'index']);
    Route::post('/laporan/buat', [ApiKeluhanController::class, 'store']);
    
    Route::get('/payment/detail', [ApiPaymentController::class, 'detail']);
    Route::post('/payment/charge', [ApiPaymentController::class, 'charge']);
    
    Route::get('/profil', [ApiAuthController::class, 'profile']);
});
