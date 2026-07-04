<?php

namespace App\Models;

use App\Core\Model;

/**
 * Tracks policy publishing and acceptance events for audit purposes.
 */
class PolicyAuditLog extends Model
{
    protected string $table = 'policy_audit_logs';

    public function logPublish(int $policyId, int $versionId, int $adminUserId): void
    {
        $this->query(
            'INSERT INTO policy_audit_logs (event_type, policy_id, version_id, user_id, note)
             VALUES (:event_type, :policy_id, :version_id, :user_id, :note)',
            [
                'event_type' => 'publish',
                'policy_id' => $policyId,
                'version_id' => $versionId,
                'user_id' => $adminUserId,
                'note' => 'Policy version published by administrator.',
            ]
        );
    }

    public function logAcceptance(int $policyId, int $versionId, int $userId): void
    {
        $this->query(
            'INSERT INTO policy_audit_logs (event_type, policy_id, version_id, user_id, note)
             VALUES (:event_type, :policy_id, :version_id, :user_id, :note)',
            [
                'event_type' => 'acceptance',
                'policy_id' => $policyId,
                'version_id' => $versionId,
                'user_id' => $userId,
                'note' => 'User accepted the published policy version.',
            ]
        );
    }

    public function entriesForPolicy(int $policyId): array
    {
        $statement = $this->query(
            'SELECT * FROM policy_audit_logs WHERE policy_id = :policy_id ORDER BY created_at DESC',
            ['policy_id' => $policyId]
        );

        return $statement->fetchAll();
    }
}
