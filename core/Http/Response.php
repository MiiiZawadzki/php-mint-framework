<?php

declare(strict_types=1);

namespace Mint\Core\Http;

class Response
{
    /**
     * Set the HTTP status code.
     *
     * @param  int  $code
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
     * @param  string  $key
     * @param  string  $value
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
     * @param  string  $view
     * @param  array<string, mixed>  $data
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
     * @param  mixed  $content
     * @param  int  $statusCode
     *
     * @return void
     */
    public function send(mixed $content, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($content);
    }

    /**
     * Redirect to a URL.
     *
     * @param  string  $url
     * @param  int  $statusCode
     *
     * @return void
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
    }
}
