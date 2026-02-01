<?php

namespace Mint\Core\Http;

class Request
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * @return array
     */
    public function getInput(): array
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];
    }

    /**
     * @return string
     */
    public function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '/';
    }
}
