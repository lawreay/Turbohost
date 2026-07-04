<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Uses the platform_settings table to store key/value configuration.
 */
class PlatformSetting
{
    private PDO $database;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Database::connection();
    }

    /**
     * Return all platform settings as key/value pairs.
     */
    public function all(): array
    {
        $statement = $this->database->query('SELECT setting_key, setting_value FROM platform_settings');
        $settings = [];

        foreach ($statement->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
