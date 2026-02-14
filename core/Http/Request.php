<?php

declare(strict_types=1);

namespace Mint\Core\Http;

class Request
{
    /**
     * Get the HTTP request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the request URI path.
     *
     * @return string
     */
    public function getUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get the request input data.
     *
     * @return array<string, mixed>
     */
    public function getInput(): array
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];
    }

    /**
     * Get the HTTP referer.
     *
     * @return string
     */
    public function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '/';
    }
}
