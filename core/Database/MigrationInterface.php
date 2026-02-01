<?php

namespace Mint\Core\Database;

use PDO;

interface MigrationInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param PDO $pdo
     * @return void
     */
    public function up(PDO $pdo): void;

    /**
     * @param PDO $pdo
     * @return void
     */
    public function down(PDO $pdo): void;
}