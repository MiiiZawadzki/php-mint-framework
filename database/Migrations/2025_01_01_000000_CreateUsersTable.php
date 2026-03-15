<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Mint\Core\Database\MigrationInterface;
use PDO;

class CreateUsersTable implements MigrationInterface
{
    /**
     * Get the migration name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'create_users_table';
    }

    /**
     * Run the migration.
     *
     * @param  PDO  $pdo
     *
     * @return void
     */
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            "
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                password TEXT NOT NULL
            )
        "
        );
    }

    /**
     * Reverse the migration.
     *
     * @param  PDO  $pdo
     *
     * @return void
     */
    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
}
