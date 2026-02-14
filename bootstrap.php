<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mint\Core\Container;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;
use Mint\Core\Http\SessionManager;

$container = Container::getInstance();

$container->singleton(Request::class);
$container->singleton(Response::class);
$container->singleton(SessionManager::class);
$container->singleton(Router::class);

return $container;
