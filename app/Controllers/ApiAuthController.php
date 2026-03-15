<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Mint\Core\Auth\Auth;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;
use Random\RandomException;

class ApiAuthController
{
    public function __construct(
        private readonly Auth $auth,
    ) {
    }

    /**
     * Handle an API login request. Returns a bearer token.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     * @throws RandomException
     */
    public function login(Request $request, Response $response): void
    {
        $input = $request->getJson();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            $response->send(['error' => 'Username and password are required.'], 422);
            return;
        }

        $user = User::findByUsername($username);

        if (!$user || !$user->verifyPassword($password)) {
            $response->send(['error' => 'Invalid credentials.'], 401);
            return;
        }

        $token = $user->generateApiToken();

        $response->send($this->userTokenPayload($user, $token));
    }

    /**
     * Handle an API registration request. Returns a bearer token.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     * @throws RandomException
     */
    public function register(Request $request, Response $response): void
    {
        $input = $request->getJson();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';

        $error = $this->validateRegistrationInput($username, $password, $passwordConfirmation);
        if ($error !== null) {
            $response->send(['error' => $error['error']], $error['status']);
            return;
        }

        $user = User::create($username, $password);
        $token = $user->generateApiToken();

        $response->send($this->userTokenPayload($user, $token), 201);
    }

    /**
     * @param  string  $username
     * @param  string  $password
     * @param  string  $confirmation
     * @return array|null
     */
    private function validateRegistrationInput(string $username, string $password, string $confirmation): ?array
    {
        if ($username === '' || $password === '') {
            return ['error' => 'Username and password are required.', 'status' => 422];
        }

        if (strlen($password) < 6) {
            return ['error' => 'Password must be at least 6 characters.', 'status' => 422];
        }

        if ($password !== $confirmation) {
            return ['error' => 'Passwords do not match.', 'status' => 422];
        }

        if (User::findByUsername($username) !== null) {
            return ['error' => 'Username already taken.', 'status' => 409];
        }

        return null;
    }

    /**
     * @param  User  $user
     * @param  string  $token
     * @return array
     */
    private function userTokenPayload(User $user, string $token): array
    {
        return ['data' => ['user' => ['id' => $user->id, 'username' => $user->username], 'token' => $token]];
    }

    /**
     * Get the currently authenticated user.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function user(Request $request, Response $response): void
    {
        $user = $this->auth->user();

        $response->send([
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
        ]);
    }
}
