<?php

namespace App\Models;

use App\Core\Model;

/**
 * Represents a legal policy grouping such as terms or privacy.
 */
class LegalPolicy extends Model
{
    protected string $table = 'legal_policies';

    public function all(): array
    {
        $statement = $this->query('SELECT * FROM legal_policies ORDER BY policy_type, created_at DESC');
        return $statement->fetchAll();
    }

    public function findByType(string $policyType): ?array
    {
        $statement = $this->query(
            'SELECT * FROM legal_policies WHERE policy_type = :policy_type LIMIT 1',
            ['policy_type' => $policyType]
        );

        $policy = $statement->fetch();
        return $policy ?: null;
    }

    public function create(string $policyType, string $title): int
    {
        $this->query(
            'INSERT INTO legal_policies (policy_type, title) VALUES (:policy_type, :title)',
            ['policy_type' => $policyType, 'title' => $title]
        );

        return (int) $this->database->lastInsertId();
    }

    public function updateTitle(int $policyId, string $title): void
    {
        $this->query(
            'UPDATE legal_policies SET title = :title WHERE id = :id',
            ['id' => $policyId, 'title' => $title]
        );
    }
}
