<?php

namespace App\Controllers;

use Mint\Core\Http\Request;
use Mint\Core\Http\Response;

class IndexController
{
    /**
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function index(Request $request, Response $response): void
    {
        $response->render(
            __DIR__ . '/../../views/index.php',
            ['version' => app_version()]
        );
    }

    /**
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function pong(Request $request, Response $response): void
    {
        $response->setStatusCode(200);
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');

        $response->send(['data' => 'pong']);
    }
}
