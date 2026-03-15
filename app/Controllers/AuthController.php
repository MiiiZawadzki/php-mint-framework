<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Mint\Core\Auth\Auth;
use Mint\Core\Http\Request;
use Mint\Core\Http\Response;

class AuthController
{
    public function __construct(
        private readonly Auth $auth,
    ) {
    }

    /**
     * Show the login form.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function showLoginForm(Request $request, Response $response): void
    {
        $this->renderLogin($response);
    }

    /**
     * Handle a login request.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function login(Request $request, Response $response): void
    {
        $input = $request->getInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->renderLogin($response, 'Username and password are required.');
            return;
        }

        if (!$this->auth->attempt($username, $password)) {
            $this->renderLogin($response, 'Invalid username or password.');
            return;
        }

        $response->redirect('/dashboard');
    }

    /**
     * Show the registration form.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function showRegisterForm(Request $request, Response $response): void
    {
        $this->renderRegister($response);
    }

    /**
     * Handle a registration request.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function register(Request $request, Response $response): void
    {
        $input = $request->getInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';

        $error = $this->validateRegistrationInput($username, $password, $passwordConfirmation);
        if ($error !== null) {
            $this->renderRegister($response, $error);
            return;
        }

        $user = User::create($username, $password);
        $this->auth->login($user);

        $response->redirect('/dashboard');
    }

    /**
     * Handle a logout request.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function logout(Request $request, Response $response): void
    {
        $this->auth->logout();
        $response->redirect('/login');
    }

    /**
     * @param  Response  $response
     * @param  string|null  $error
     * @return void
     */
    private function renderLogin(Response $response, ?string $error = null): void
    {
        $response->render(__DIR__ . '/../../views/auth/login.php', ['error' => $error]);
    }

    /**
     * @param  Response  $response
     * @param  string|null  $error
     * @return void
     */
    private function renderRegister(Response $response, ?string $error = null): void
    {
        $response->render(__DIR__ . '/../../views/auth/register.php', ['error' => $error]);
    }

    /**
     * @param  string  $username
     * @param  string  $password
     * @param  string  $confirmation
     * @return string|null
     */
    private function validateRegistrationInput(string $username, string $password, string $confirmation): ?string
    {
        if ($username === '' || $password === '') {
            return 'Username and password are required.';
        }

        if (strlen($password) < 6) {
            return 'Password must be at least 6 characters.';
        }

        if ($password !== $confirmation) {
            return 'Passwords do not match.';
        }

        if (User::findByUsername($username) !== null) {
            return 'Username already taken.';
        }

        return null;
    }
}
