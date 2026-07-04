<?php
// Non-destructive simulation: create in-app notifications for all users about published policies.
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/constants.php';

use App\Models\LegalPolicy;
use App\Models\LegalPolicyVersion;
use App\Models\User;
use App\Services\NotificationService;

$policyModel = new LegalPolicy();
$versionModel = new LegalPolicyVersion();
$notificationService = new NotificationService(require __DIR__ . '/../app/Config/app.php');

$policies = $policyModel->all();
$published = [];
foreach ($policies as $p) {
    $pub = $versionModel->publishedVersion((int) $p['id']);
    if ($pub) {
        $published[] = [
            'policy' => $p,
            'published' => $pub,
        ];
    }
}

if (empty($published)) {
    echo "No published policies found.\n";
    exit(0);
}

$users = (new User())->allIds();
$created = 0;
foreach ($users as $u) {
    $userId = (int) ($u['id'] ?? 0);
    if ($userId <= 0) continue;

    $title = 'Platform policies updated';
    $message = 'Please review and accept the updated platform policies on your dashboard.';
    try {
        $notificationService->createNotification($userId, $title, $message, 'legal', 'file-text', '/legal/accept', false, false);
        $created++;
    } catch (\Throwable $e) {
        // continue
    }
}

echo "Created {$created} in-app notifications.\n";
exit(0);
