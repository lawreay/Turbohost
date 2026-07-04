<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Stores and reads platform settings from the database.
 */
class Setting
{
    private PDO $database;

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Database::connection();
    }

    /**
     * Return all saved settings as key/value pairs.
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

    /**
     * Save multiple settings using an upsert.
     */
    public function saveMany(array $settings): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO platform_settings (setting_key, setting_value, setting_group)
             VALUES (:setting_key, :setting_value, :setting_group)
             ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_group = VALUES(setting_group),
                updated_at = CURRENT_TIMESTAMP'
        );

        foreach ($settings as $key => $meta) {
            $statement->execute([
                'setting_key' => $key,
                'setting_value' => $meta['value'],
                'setting_group' => $meta['group'],
            ]);
        }
    }
}
