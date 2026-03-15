<?php

declare(strict_types=1);

namespace App\Controllers;

use Mint\Core\Auth\Auth;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;

readonly class DashboardController
{
    public function __construct(
        private Auth $auth,
    ) {
    }

    /**
     * Show the dashboard page.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function index(Request $request, Response $response): void
    {
        $response->render(__DIR__ . '/../../views/dashboard.php', [
            'user' => $this->auth->user(),
        ]);
    }
}
