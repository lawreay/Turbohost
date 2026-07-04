<?php

namespace App\Services;

use App\Models\LegalPolicy;
use App\Models\LegalPolicyVersion;
use App\Models\UserPolicyAcceptance;

/**
 * Encapsulates legal policy lookup and acceptance logic.
 */
class LegalService
{
    private LegalPolicy $policyModel;
    private LegalPolicyVersion $versionModel;
    private UserPolicyAcceptance $acceptanceModel;

    public function __construct()
    {
        $this->policyModel = new LegalPolicy();
        $this->versionModel = new LegalPolicyVersion();
        $this->acceptanceModel = new UserPolicyAcceptance();
    }

    public function latestPublishedPolicies(): array
    {
        $policies = $this->policyModel->all();
        $published = [];

        foreach ($policies as $policy) {
            $version = $this->versionModel->publishedVersion((int) $policy['id']);
            if (!$version) {
                continue;
            }

            $published[] = [
                'policy_id' => (int) $policy['id'],
                'policy_type' => $policy['policy_type'],
                'title' => $policy['title'],
                'version_id' => (int) $version['id'],
                'version_label' => $version['version_label'],
                'content' => $version['content'],
                'published_at' => $version['created_at'],
            ];
        }

        return $published;
    }

    public function needsAgreement(int $userId): bool
    {
        $publishedPolicies = $this->latestPublishedPolicies();

        foreach ($publishedPolicies as $policy) {
            $acceptance = $this->acceptanceModel->lastAcceptedVersion($userId, $policy['policy_id']);
            if (!$acceptance || (int) $acceptance['version_id'] !== (int) $policy['version_id']) {
                return true;
            }
        }

        return false;
    }

    public function pendingPolicyTypes(int $userId): array
    {
        $pending = [];
        $publishedPolicies = $this->latestPublishedPolicies();

        foreach ($publishedPolicies as $policy) {
            $acceptance = $this->acceptanceModel->lastAcceptedVersion($userId, $policy['policy_id']);
            if (!$acceptance || (int) $acceptance['version_id'] !== (int) $policy['version_id']) {
                $pending[] = $policy;
            }
        }

        return $pending;
    }
}
