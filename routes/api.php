<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TransactionController;

// Public routes
Route::middleware(['throttle:5,1'])->post('/auth/login', [AuthController::class, 'login']);
Route::middleware(['throttle:10,1'])->post('/purchases', [PurchaseController::class, 'store']);

// Private routes
Route::middleware(['auth.jwt'])->group(function () {
    // Admin only - Gateway management
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::patch('gateways/{gateway}/toggle', [GatewayController::class, 'toggle']);
        Route::patch('gateways/{gateway}/priority', [GatewayController::class, 'updatePriority']);
    });

    // Admin, Manager - User management
    Route::middleware(['role:ADMIN,MANAGER'])->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Admin, Manager, Finance - Product management
    Route::middleware(['role:ADMIN,MANAGER,FINANCE'])->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    // Admin, Finance - Refund
    Route::middleware(['role:ADMIN,FINANCE'])->group(function () {
        Route::post('transactions/{transaction}/refund', [TransactionController::class, 'refund']);
    });

    // All authenticated - Clients and Transactions
    Route::middleware(['role:ADMIN,MANAGER,FINANCE,USER'])->group(function () {
        Route::get('clients', [ClientController::class, 'index']);
        Route::get('clients/{client}', [ClientController::class, 'show']);
        Route::get('transactions', [TransactionController::class, 'index']);
        Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    });
});
