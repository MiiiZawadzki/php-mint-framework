<?php

declare(strict_types=1);

namespace Mint\Core\Database;

use PDO;

class MigrationRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            )
        ");
    }

    /**
     * Get the list of executed migration names.
     *
     * @return array<int, string>
     */
    public function getExecuted(): array
    {
        $migrationsQuery = $this->pdo->query("SELECT name FROM migrations");

        return $migrationsQuery->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Mark a migration as executed.
     *
     * @param string $name
     *
     * @return void
     */
    public function markAsExecuted(string $name): void
    {
        $migrationsInsertQuery = $this->pdo->prepare("INSERT INTO migrations (name) VALUES (?)");
        $migrationsInsertQuery->execute([$name]);
    }
}
