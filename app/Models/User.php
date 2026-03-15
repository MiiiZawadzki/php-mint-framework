<?php

declare(strict_types=1);

namespace App\Models;

use Mint\Core\Database\Database;
use PDO;
use Random\RandomException;

readonly class User
{
    /**
     * @param  int  $id
     * @param  string  $username
     * @param  string  $password
     * @param  string|null  $apiToken
     */
    public function __construct(
        public int $id,
        public string $username,
        private string $password,
        public ?string $apiToken = null,
    ) {
    }

    /**
     * Create a new user.
     *
     * @param  string  $username
     * @param  string  $password
     *
     * @return static
     */
    public static function create(string $username, string $password): static
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $id = (int)$pdo->lastInsertId();

        return new static($id, $username, '');
    }

    /**
     * Find a user by ID.
     *
     * @param  int  $id
     *
     * @return static|null
     */
    public static function findById(int $id): ?static
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return static::fromRow($row);
    }

    /**
     * Find a user by username.
     *
     * @param  string  $username
     *
     * @return static|null
     */
    public static function findByUsername(string $username): ?static
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return static::fromRow($row);
    }

    /**
     * Find a user by API token.
     *
     * @param  string  $token
     *
     * @return static|null
     */
    public static function findByToken(string $token): ?static
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE api_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return static::fromRow($row);
    }

    /**
     * Verify a password against the stored hash.
     *
     * @param  string  $password
     *
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Generate and store an API token for this user.
     *
     * @return string
     * @throws RandomException
     */
    public function generateApiToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET api_token = :token WHERE id = :id');
        $stmt->execute(['token' => $token, 'id' => $this->id]);

        return $token;
    }

    /**
     * Create a User instance from a database row.
     *
     * @param  array<string, mixed>  $row
     *
     * @return static
     */
    private static function fromRow(array $row): static
    {
        return new static(
            id: (int)$row['id'],
            username: $row['username'],
            password: $row['password'],
            apiToken: $row['api_token'] ?? null,
        );
    }
}
