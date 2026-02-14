<?php

use App\Controllers\IndexController;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;

$container = require __DIR__ . '/../bootstrap.php';

$router = $container->make(Router::class);

$router->get('/', [IndexController::class, 'index']);

$router->dispatch(
    $container->make(Request::class),
    $container->make(Response::class)
);
