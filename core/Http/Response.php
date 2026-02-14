<?php

declare(strict_types=1);

namespace Mint\Core\Http;

class Response
{
    /**
     * Set the HTTP status code.
     *
     * @param int $code
     *
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Set an HTTP header.
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    public function setHeader(string $key, string $value): void
    {
        header("$key: $value");
    }

    /**
     * Render a view file with data.
     *
     * @param string               $view
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function render(string $view, array $data = []): void
    {
        extract($data);
        require $view;
    }

    /**
     * Send JSON-encoded content.
     *
     * @param mixed $content
     *
     * @return void
     */
    public function send(mixed $content): void
    {
        echo json_encode($content);
    }
}
