<?php

declare(strict_types=1);

namespace Mint\Core\Database;

use PDO;

class Database
{
    /**
     * Shared PDO connection instance.
     *
     * @var PDO|null
     */
    private static ?PDO $connection = null;

    /**
     * Get the database connection.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$connection = new PDO('sqlite:' . __DIR__ . '/../../database/database.sqlite');
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$connection;
    }
}
