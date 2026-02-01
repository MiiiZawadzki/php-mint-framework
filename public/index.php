<?php

use App\Controllers\IndexController;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Mint\Core\Http\Router;

require __DIR__.'/../bootstrap.php';

$router = new Router();

$router->get('/', [new IndexController(), 'index']);

$router->dispatch(new Request(), new Response());
