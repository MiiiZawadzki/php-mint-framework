<?php

declare(strict_types=1);

namespace Mint\Core\Http;

/**
 * Interface for HTTP middleware.
 */
interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  callable  $next
     *
     * @return void
     */
    public function handle(Request $request, callable $next): void;
}
