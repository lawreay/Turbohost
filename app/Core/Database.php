<?php

namespace App\Core;

use PDO;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'constants.php';

/**
 * Creates and shares the PDO database connection.
 */
final class Database
{
    private static ?PDO $connection = null;

    /**
     * Return a shared PDO connection configured for secure MySQL access.
     */
    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require APP_PATH . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'database.php';

        self::$connection = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            $config['options']
        );

        return self::$connection;
    }
}
