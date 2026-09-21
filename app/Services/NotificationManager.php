<?php

namespace App\Services;

use App\Models\Notification as NotificationModel;

/**
 * Centralized notification manager for event-driven workflows.
 */
class NotificationManager
{
    private NotificationService $service;
    private NotificationModel $notificationModel;

    public function __construct(array $appConfig = [])
    {
        $this->service = new NotificationService($appConfig);
        $this->notificationModel = new NotificationModel();
    }

    public function broadcastAnnouncement(string $title, string $message, string $category = 'broadcast', string $icon = 'send', ?string $targetUrl = null): void
    {
        $this->service->broadcast($title, $message, $category, $icon, $targetUrl);
    }

    public function sendUserNotification(int $userId, string $title, string $message, array $options = []): int
    {
        return $this->service->createNotification(
            $userId,
            $title,
            $message,
            $options['category'] ?? 'general',
            $options['icon'] ?? 'bell',
            $options['target_url'] ?? null,
            $options['is_read'] ?? false,
            $options['email_sent'] ?? false
        );
    }

    public function sendUserNotificationWithEmail(int $userId, string $title, string $message, string $subject, string $templateName, array $templateData = [], array $options = []): void
    {
        $this->service->notifyUserWithEmail(
            $userId,
            $title,
            $message,
            $subject,
            $templateName,
            $templateData,
            $options['category'] ?? 'general',
            $options['icon'] ?? 'bell',
            $options['target_url'] ?? null
        );
    }

    public function notifyAdminAlert(string $title, string $message, array $options = []): int
    {
        return $this->service->notifyAdmin(
            $title,
            $message,
            $options['category'] ?? 'admin',
            $options['icon'] ?? 'shield-alert',
            $options['target_url'] ?? null
        );
    }

    public function latestForUser(int $userId, int $limit = 10): array
    {
        return $this->notificationModel->latestForUser($userId, $limit);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notificationModel->unreadCountForUser($userId);
    }

    public function trigger(string $event, array $payload = []): void
    {
        switch ($event) {
            case 'user.registered':
                $userId = (int) ($payload['user_id'] ?? 0);
                if ($userId === 0) {
                    return;
                }

                $this->sendUserNotification(
                    $userId,
                    'Registration successful',
                    'Your account has been created. Verify your email to unlock the dashboard.',
                    [
                        'category' => 'security',
                        'icon' => 'check-circle',
                        'target_url' => '/login',
                    ]
                );

                $this->notifyAdminAlert(
                    'New user registered',
                    sprintf('A new user registered: %s (%s)', $payload['fullname'] ?? 'Unknown', $payload['email'] ?? 'Unknown'),
                    [
                        'category' => 'admin',
                        'icon' => 'user-plus',
                        'target_url' => '/admin/users',
                    ]
                );
                break;

            case 'password.reset_requested':
                $userId = (int) ($payload['user_id'] ?? 0);
                if ($userId === 0) {
                    return;
                }

                $this->sendUserNotificationWithEmail(
                    $userId,
                    'Password reset requested',
                    'A password reset was requested for your Instaweb account.',
                    'Password reset requested',
                    'password-reset',
                    ['message' => 'A password reset was requested for your Instaweb account.'],
                    [
                        'category' => 'security',
                        'icon' => 'key',
                        'target_url' => '/forgot-password',
                    ]
                );
                break;

            default:
                // No-op for unsupported events. This method is a safe extension point.
                break;
        }
    }
}
