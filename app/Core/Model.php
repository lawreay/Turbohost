<?php

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Base model with prepared statement helpers.
 */
abstract class Model
{
    protected PDO $database;
    protected string $table = '';

    public function __construct(?PDO $database = null)
    {
        $this->database = $database ?? Database::connection();
    }

    /**
     * Run a prepared SQL statement.
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->database->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Find one table row by primary key.
     */
    public function find(int $id): ?array
    {
        $statement = $this->query("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $statement->fetch();

        return $row ?: null;
    }
}
