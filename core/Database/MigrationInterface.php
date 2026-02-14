<?php

declare(strict_types=1);

namespace Mint\Core\Database;

use PDO;

/**
 * Interface for database migrations.
 */
interface MigrationInterface
{
    /**
     * Get the migration name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Run the migration.
     *
     * @param PDO $pdo
     *
     * @return void
     */
    public function up(PDO $pdo): void;

    /**
     * Reverse the migration.
     *
     * @param PDO $pdo
     *
     * @return void
     */
    public function down(PDO $pdo): void;
}