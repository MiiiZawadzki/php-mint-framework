<?php

declare(strict_types=1);

use Mint\Core\Http\Request;

beforeEach(function () {
    // Reset superglobals
    $_SERVER = [];
    $_POST = [];
});

describe('Request', function () {
    describe('getMethod', function () {
        it('returns GET method', function () {
            $_SERVER['REQUEST_METHOD'] = 'GET';

            $request = new Request();

            expect($request->getMethod())->toBe('GET');
        });

        it('returns POST method', function () {
            $_SERVER['REQUEST_METHOD'] = 'POST';

            $request = new Request();

            expect($request->getMethod())->toBe('POST');
        });

        it('returns PUT method', function () {
            $_SERVER['REQUEST_METHOD'] = 'PUT';

            $request = new Request();

            expect($request->getMethod())->toBe('PUT');
        });

        it('returns DELETE method', function () {
            $_SERVER['REQUEST_METHOD'] = 'DELETE';

            $request = new Request();

            expect($request->getMethod())->toBe('DELETE');
        });
    });

    describe('getUri', function () {
        it('returns simple URI path', function () {
            $_SERVER['REQUEST_URI'] = '/users';

            $request = new Request();

            expect($request->getUri())->toBe('/users');
        });

        it('returns root path', function () {
            $_SERVER['REQUEST_URI'] = '/';

            $request = new Request();

            expect($request->getUri())->toBe('/');
        });

        it('returns nested path', function () {
            $_SERVER['REQUEST_URI'] = '/api/users/123';

            $request = new Request();

            expect($request->getUri())->toBe('/api/users/123');
        });

        it('strips query string from URI', function () {
            $_SERVER['REQUEST_URI'] = '/users?page=1&sort=name';

            $request = new Request();

            expect($request->getUri())->toBe('/users');
        });

        it('strips fragment from URI', function () {
            $_SERVER['REQUEST_URI'] = '/page#section';

            $request = new Request();

            expect($request->getUri())->toBe('/page');
        });

        it('handles URI with query string and fragment', function () {
            $_SERVER['REQUEST_URI'] = '/search?q=test#results';

            $request = new Request();

            expect($request->getUri())->toBe('/search');
        });
    });

    describe('getInput', function () {
        it('returns POST data for POST request', function () {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = ['name' => 'John', 'email' => 'john@example.com'];

            $request = new Request();

            expect($request->getInput())->toBe(['name' => 'John', 'email' => 'john@example.com']);
        });

        it('returns empty array for GET request', function () {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_POST = ['name' => 'John'];

            $request = new Request();

            expect($request->getInput())->toBe([]);
        });

        it('returns empty array for PUT request', function () {
            $_SERVER['REQUEST_METHOD'] = 'PUT';
            $_POST = ['data' => 'value'];

            $request = new Request();

            expect($request->getInput())->toBe([]);
        });

        it('returns empty array when no POST data', function () {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST = [];

            $request = new Request();

            expect($request->getInput())->toBe([]);
        });
    });

    describe('getHeader', function () {
        it('returns a specific header value', function () {
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer abc123';

            $request = new Request();

            expect($request->getHeader('Authorization'))->toBe('Bearer abc123');
        });

        it('returns null for missing header', function () {
            $request = new Request();

            expect($request->getHeader('X-Custom'))->toBeNull();
        });

        it('handles hyphenated header names', function () {
            $_SERVER['HTTP_X_CUSTOM_HEADER'] = 'custom-value';

            $request = new Request();

            expect($request->getHeader('X-Custom-Header'))->toBe('custom-value');
        });
    });

    describe('getQuery', function () {
        it('returns query parameters', function () {
            $_GET = ['page' => '2', 'sort' => 'name'];

            $request = new Request();

            expect($request->getQuery())->toBe(['page' => '2', 'sort' => 'name']);

            $_GET = [];
        });

        it('returns empty array when no query params', function () {
            $_GET = [];

            $request = new Request();

            expect($request->getQuery())->toBe([]);
        });
    });

    describe('getReferer', function () {
        it('returns referer when set', function () {
            $_SERVER['HTTP_REFERER'] = 'https://example.com/previous';

            $request = new Request();

            expect($request->getReferer())->toBe('https://example.com/previous');
        });

        it('returns default slash when referer not set', function () {
            // HTTP_REFERER not set

            $request = new Request();

            expect($request->getReferer())->toBe('/');
        });

        it('returns internal path referer', function () {
            $_SERVER['HTTP_REFERER'] = '/dashboard';

            $request = new Request();

            expect($request->getReferer())->toBe('/dashboard');
        });
    });
});
