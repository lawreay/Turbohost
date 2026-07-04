<?php

namespace App\Models;

use App\Core\Model;

/**
 * Stores versioned legal policy content.
 */
class LegalPolicyVersion extends Model
{
    protected string $table = 'legal_policy_versions';

    public function create(int $policyId, string $versionLabel, string $content, bool $published): int
    {
        $this->query(
            'INSERT INTO legal_policy_versions (policy_id, version_label, content, published)
             VALUES (:policy_id, :version_label, :content, :published)',
            [
                'policy_id' => $policyId,
                'version_label' => $versionLabel,
                'content' => $content,
                'published' => $published ? 1 : 0,
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    public function publishedVersion(int $policyId): ?array
    {
        $statement = $this->query(
            'SELECT * FROM legal_policy_versions WHERE policy_id = :policy_id AND published = 1 ORDER BY created_at DESC LIMIT 1',
            ['policy_id' => $policyId]
        );

        $version = $statement->fetch();
        return $version ?: null;
    }

    public function versionsForPolicy(int $policyId): array
    {
        $statement = $this->query(
            'SELECT * FROM legal_policy_versions WHERE policy_id = :policy_id ORDER BY created_at DESC',
            ['policy_id' => $policyId]
        );

        return $statement->fetchAll();
    }

    public function find(int $versionId): ?array
    {
        $statement = $this->query('SELECT * FROM legal_policy_versions WHERE id = :id LIMIT 1', ['id' => $versionId]);
        $version = $statement->fetch();
        return $version ?: null;
    }

    public function publish(int $versionId): void
    {
        $version = $this->find($versionId);
        if (!$version) {
            return;
        }

        $this->query('UPDATE legal_policy_versions SET published = 0 WHERE policy_id = :policy_id', ['policy_id' => $version['policy_id']]);
        $this->query('UPDATE legal_policy_versions SET published = 1 WHERE id = :id', ['id' => $versionId]);
    }
}
