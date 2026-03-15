<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Mint\Core\Database\MigrationInterface;
use PDO;

class AddApiTokenToUsersTable implements MigrationInterface
{
    /**
     * Get the migration name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'add_api_token_to_users_table';
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
        $pdo->exec('ALTER TABLE users ADD COLUMN api_token TEXT DEFAULT NULL');
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
        $pdo->exec('ALTER TABLE users DROP COLUMN api_token');
    }
}
