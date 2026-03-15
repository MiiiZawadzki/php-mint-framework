<?php

declare(strict_types=1);

use App\Controllers\ApiAuthController;
use App\Controllers\IndexController;
use Mint\Core\Facades\Route;
use Mint\Core\Http\Middleware\ApiAuthMiddleware;

Route::get('/api/ping', [IndexController::class, 'pong']);

// Public API auth routes
Route::post('/api/login', [ApiAuthController::class, 'login']);
Route::post('/api/register', [ApiAuthController::class, 'register']);

// Protected API routes
Route::middleware([ApiAuthMiddleware::class], function () {
    Route::get('/api/user', [ApiAuthController::class, 'user']);
});
