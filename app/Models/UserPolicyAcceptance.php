<?php

namespace App\Models;

use App\Core\Model;

/**
 * Records user acceptance of published policy versions.
 */
class UserPolicyAcceptance extends Model
{
    protected string $table = 'user_policy_acceptances';

    public function lastAcceptedVersion(int $userId, int $policyId): ?array
    {
        $statement = $this->query(
            'SELECT * FROM user_policy_acceptances
             WHERE user_id = :user_id AND policy_id = :policy_id
             ORDER BY accepted_at DESC
             LIMIT 1',
            ['user_id' => $userId, 'policy_id' => $policyId]
        );

        $row = $statement->fetch();
        return $row ?: null;
    }

    public function recordAcceptance(int $userId, int $policyId, int $versionId): void
    {
        $this->query(
            'INSERT INTO user_policy_acceptances (user_id, policy_id, version_id, accepted_at)
             VALUES (:user_id, :policy_id, :version_id, NOW())',
            ['user_id' => $userId, 'policy_id' => $policyId, 'version_id' => $versionId]
        );
    }
}
