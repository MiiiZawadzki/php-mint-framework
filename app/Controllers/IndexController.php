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
            __DIR__.'/../../views/index.php',
            ['version' => app_version()]
        );
    }
}
