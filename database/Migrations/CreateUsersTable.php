<?php

namespace App\Database\Migrations;

use Mint\Core\Database\MigrationInterface;
use PDO;

class CreateUsersTable implements MigrationInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'create_users_table';
    }

    /**
     * @param  PDO  $pdo
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
     * @param  PDO  $pdo
     * @return void
     */
    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
}
