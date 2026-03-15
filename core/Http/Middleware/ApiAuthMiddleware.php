<?php

declare(strict_types=1);

namespace Mint\Core\Http\Middleware;

use Mint\Core\Auth\Auth;
use Mint\Core\Http\MiddlewareInterface;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;

/**
 * Middleware that requires a valid API token (Bearer token in Authorization header).
 * Returns 401 JSON response if not authenticated.
 */
readonly class ApiAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Auth $auth,
        private Response $response,
    ) {
    }

    /**
     * @param  Request  $request
     * @param  callable  $next
     * @return void
     */
    public function handle(Request $request, callable $next): void
    {
        if ($this->auth->guest()) {
            $this->response->send(['error' => 'Unauthenticated.'], 401);
            return;
        }

        $next();
    }
}
