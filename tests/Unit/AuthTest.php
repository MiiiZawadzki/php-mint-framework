<?php

declare(strict_types=1);

use Mint\Core\Auth\Auth;
use Mint\Core\Http\Request;
use Mint\Core\Http\SessionManager;

beforeEach(function () {
    // Set up an in-memory SQLite database for testing
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->exec(
        '
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            api_token TEXT DEFAULT NULL
        )
    '
    );

    // Insert a test user
    $hash = password_hash('secret123', PASSWORD_DEFAULT);
    $this->pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)')->execute(['testuser', $hash]);
});

describe('Auth', function () {
    describe('session-based auth', function () {
        it('attempt succeeds with valid credentials', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->attempt('testuser', 'secret123'))->toBeTrue();
            expect($auth->check())->toBeTrue();
            expect($auth->user()->username)->toBe('testuser');
        });

        it('attempt fails with wrong password', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->attempt('testuser', 'wrongpassword'))->toBeFalse();
            expect($auth->check())->toBeFalse();
        });

        it('attempt fails with non-existent user', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->attempt('nobody', 'secret123'))->toBeFalse();
        });

        it('login stores user in session', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            $auth->attempt('testuser', 'secret123');

            expect($session->get('auth_user_id'))->toBe(1);
        });

        it('logout clears user from session', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            $auth->attempt('testuser', 'secret123');
            expect($auth->check())->toBeTrue();

            $auth->logout();
            expect($auth->check())->toBeFalse();
            expect($session->has('auth_user_id'))->toBeFalse();
        });

        it('guest returns true when not authenticated', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->guest())->toBeTrue();
        });

        it('guest returns false when authenticated', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            $auth->attempt('testuser', 'secret123');

            expect($auth->guest())->toBeFalse();
        });

        it('id returns user id when authenticated', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            $auth->attempt('testuser', 'secret123');

            expect($auth->id())->toBe(1);
        });

        it('id returns null when not authenticated', function () {
            $session = createTestSession();
            $request = createAuthMockRequest();
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->id())->toBeNull();
        });
    });

    describe('token-based auth', function () {
        it('authenticates via bearer token in header', function () {
            // Generate a token for the test user
            $token = bin2hex(random_bytes(32));
            $this->pdo->prepare('UPDATE users SET api_token = ? WHERE id = 1')->execute([$token]);

            $session = createTestSession();
            $request = createAuthMockRequest('GET', '/', ['Authorization' => "Bearer $token"]);
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->check())->toBeTrue();
            expect($auth->user()->username)->toBe('testuser');
        });

        it('rejects invalid bearer token', function () {
            $session = createTestSession();
            $request = createAuthMockRequest('GET', '/', ['Authorization' => 'Bearer invalidtoken']);
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->check())->toBeFalse();
        });

        it('ignores non-bearer authorization header', function () {
            $session = createTestSession();
            $request = createAuthMockRequest('GET', '/', ['Authorization' => 'Basic dXNlcjpwYXNz']);
            $auth = createAuthWithDb($this->pdo, $session, $request);

            expect($auth->check())->toBeFalse();
        });
    });
});

// Helper functions for auth tests

/**
 * Creates a mock session that stores data in a plain array (no actual PHP session).
 */
function createTestSession(): SessionManager
{
    // Use a simple in-memory stub instead of real sessions
    return new class extends SessionManager {
        private array $data = [];

        public function __construct()
        {
            // Do NOT call parent — avoid session_start() in tests
        }

        public function set(string $key, mixed $value): void
        {
            $this->data[$key] = $value;
        }

        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }

        public function has(string $key): bool
        {
            return isset($this->data[$key]);
        }

        public function remove(string $key): void
        {
            unset($this->data[$key]);
        }

        public function destroy(): void
        {
            $this->data = [];
        }
    };
}

function createAuthMockRequest(string $method = 'GET', string $uri = '/', array $headers = []): Request
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $uri;

    // Clear old HTTP_ headers
    foreach (array_keys($_SERVER) as $key) {
        if (str_starts_with($key, 'HTTP_')) {
            unset($_SERVER[$key]);
        }
    }

    // Set headers
    foreach ($headers as $name => $value) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $_SERVER[$key] = $value;
    }

    return new Request();
}

/**
 * Creates an Auth instance wired to the given test PDO.
 *
 * We override the Database connection so User model queries use our in-memory DB.
 */
function createAuthWithDb(PDO $pdo, SessionManager $session, Request $request): Auth
{
    // Use reflection to inject our test PDO into the Database singleton
    $ref = new ReflectionClass(\Mint\Core\Database\Database::class);
    $prop = $ref->getProperty('connection');
    $prop->setAccessible(true);
    $prop->setValue(null, $pdo);

    return new Auth($session, $request);
}
