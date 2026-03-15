<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\IndexController;
use Mint\Core\Facades\Route;
use Mint\Core\Http\Middleware\AuthMiddleware;
use Mint\Core\Http\Middleware\GuestMiddleware;

Route::get('/', [IndexController::class, 'index']);

// Guest routes (redirect to dashboard if already authenticated)
Route::middleware([GuestMiddleware::class], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes (redirect to login if not authenticated)
Route::middleware([AuthMiddleware::class], function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
