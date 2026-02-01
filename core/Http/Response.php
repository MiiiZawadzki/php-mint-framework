<?php

namespace Mint\Core\Http;

class Response
{
    /**
     * @param int $code
     * @return void
     */
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setHeader(string $key, string $value): void
    {
        header("$key: $value");
    }

    /**
     * @param string $view
     * @param array $data
     * @return void
     */
    public function render(string $view, array $data = []): void
    {
        extract($data);
        require $view;
    }

    /**
     * @param mixed $content
     * @return void
     */
    public function send(mixed $content): void
    {
        echo json_encode($content);
    }
}
