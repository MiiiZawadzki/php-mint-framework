<?php

declare(strict_types=1);

use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;

$container = require __DIR__ . '/../bootstrap.php';

$container->make(Router::class)->dispatch(
    $container->make(Request::class),
    $container->make(Response::class)
);
