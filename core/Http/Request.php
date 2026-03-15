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
     * Get query parameters.
     *
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $_GET;
    }

    /**
     * Get a specific HTTP header.
     *
     * @param  string  $name
     *
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        // Convert header name to $_SERVER format: Authorization -> HTTP_AUTHORIZATION
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return $_SERVER[$key] ?? null;
    }

    /**
     * Get the JSON-decoded request body.
     *
     * @return array<string, mixed>
     */
    public function getJson(): array
    {
        $body = file_get_contents('php://input');

        if ($body === false || $body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
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
