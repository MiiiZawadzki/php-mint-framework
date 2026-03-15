<?php

declare(strict_types=1);

namespace Mint\Core\Auth;

use App\Models\User;
use Mint\Core\Http\Request;
use Mint\Core\Http\SessionManager;

class Auth
{
    /**
     * Cached authenticated user for the current request.
     */
    private ?User $user = null;

    /**
     * Whether the user has been resolved for this request.
     */
    private bool $resolved = false;

    public function __construct(
        private readonly SessionManager $session,
        private readonly Request $request,
    ) {
    }

    /**
     * Attempt to authenticate with credentials.
     *
     * @param  string  $username
     * @param  string  $password
     *
     * @return bool
     */
    public function attempt(string $username, string $password): bool
    {
        $user = User::findByUsername($username);

        if (!$user || !$user->verifyPassword($password)) {
            return false;
        }

        $this->login($user);

        return true;
    }

    /**
     * Log in a user (store in session).
     *
     * @param  User  $user
     *
     * @return void
     */
    public function login(User $user): void
    {
        $this->session->set('auth_user_id', $user->id);
        $this->user = $user;
        $this->resolved = true;
    }

    /**
     * Log out the current user.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->remove('auth_user_id');
        $this->user = null;
        $this->resolved = true;
    }

    /**
     * Check if a user is authenticated (session or token).
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null
     */
    public function user(): ?User
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        // Try session-based auth
        $userId = $this->session->get('auth_user_id');
        if ($userId !== null) {
            $this->user = User::findById((int)$userId);
            return $this->user;
        }

        // Try token-based auth
        $header = $this->request->getHeader('Authorization');
        if ($header !== null && str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);
            $this->user = User::findByToken($token);
            return $this->user;
        }

        return null;
    }

    /**
     * Get the authenticated user's ID.
     *
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->user()?->id;
    }

    /**
     * Check if the current request is a guest (not authenticated).
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }
}
