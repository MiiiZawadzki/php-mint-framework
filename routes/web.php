<?php

declare(strict_types=1);

use App\Controllers\IndexController;
use Mint\Core\Facades\Route;

Route::get('/', [IndexController::class, 'index']);
