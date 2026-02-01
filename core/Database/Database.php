<?php

namespace Mint\Core\Database;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    /**
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
