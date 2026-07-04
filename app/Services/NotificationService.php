<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Throwable;

/**
 * Creates in-app notifications and sends optional email messages.
 */
class NotificationService
{
    private Notification $notificationModel;
    private array $settings;
    private MailerService $mailer;
    private array $appConfig;

    public function __construct(array $appConfig = [])
    {
        $this->notificationModel = new Notification();
        $this->settings = (new Setting())->all();
        $this->appConfig = $appConfig;
        $this->mailer = new MailerService($this->resolveMailConfig(), $this->appConfig);
    }

    private function resolveMailConfig(): array
    {
        $default = require APP_PATH . '/Config/mail.php';

        return [
            'host' => $this->settings['mail_host'] ?? $default['host'],
            'port' => (int) ($this->settings['mail_port'] ?? $default['port']),
            'username' => $this->settings['mail_username'] ?? $default['username'],
            'password' => $this->settings['mail_password'] ?? $default['password'],
            'encryption' => $this->settings['mail_encryption'] ?? $default['encryption'],
            'from_address' => $this->settings['mail_from_address'] ?? $default['from_address'],
            'from_name' => $this->settings['mail_from_name'] ?? $default['from_name'],
            'admin_login_notification_address' => $this->settings['admin_login_notification_email'] ?? $default['admin_login_notification_address'],
            'admin_login_notification_message' => $this->settings['admin_login_notification_message'] ?? $default['admin_login_notification_message'],
        ];
    }

    /**
     * Create a notification record for a user or system audience.
     */
    public function createNotification(?int $userId, string $title, string $message, string $category = 'general', string $icon = 'bell', ?string $targetUrl = null, bool $isRead = false, bool $emailSent = false): int
    {
        return $this->notificationModel->create($userId, $title, $message, $category, $icon, $targetUrl, $isRead, $emailSent);
    }

    /**
     * Send an email and persist a notification for the user if enabled.
     */
    public function notifyUserWithEmail(int $userId, string $title, string $message, string $subject, string $templateName, array $templateData = [], string $category = 'general', string $icon = 'bell', ?string $targetUrl = null): void
    {
        $emailSent = false;
        $user = (new User())->find($userId);

        if ($user && $this->shouldSendEmail()) {
            try {
                $html = $this->renderEmailTemplate($templateName, array_merge($templateData, ['name' => $user['fullname'], 'message' => $message]));
                $text = strip_tags($html);
                $emailSent = $this->mailer->send($user['email'], $user['fullname'], $subject, $html, $text);
            } catch (Throwable) {
                $emailSent = false;
            }
        }

        $this->createNotification($userId, $title, $message, $category, $icon, $targetUrl, false, $emailSent);
    }

    /**
     * Send an email to the user without creating a notification record only when email delivery is required.
     */
    public function sendEmailOnly(int $userId, string $subject, string $templateName, array $templateData = []): bool
    {
        if (!$this->shouldSendEmail()) {
            return false;
        }

        $user = (new User())->find($userId);
        if (!$user) {
            return false;
        }

        try {
            $html = $this->renderEmailTemplate($templateName, array_merge($templateData, ['name' => $user['fullname']]));
            $text = strip_tags($html);
            return $this->mailer->send($user['email'], $user['fullname'], $subject, $html, $text);
        } catch (Throwable) {
            return false;
        }
    }

    public function notifyAdmin(string $title, string $message, string $category = 'admin', string $icon = 'shield-alert', ?string $targetUrl = null): int
    {
        return $this->createNotification(null, $title, $message, $category, $icon, $targetUrl, false);
    }

    public function broadcast(string $title, string $message, string $category = 'broadcast', string $icon = 'send', ?string $targetUrl = null): void
    {
        $users = (new User())->allIds();
        foreach ($users as $user) {
            $this->createNotification((int) $user['id'], $title, $message, $category, $icon, $targetUrl, false);
        }
    }

    public function sendStorageThresholdNotifications(int $userId, int $previousBytes, int $currentBytes, ?int $limitBytes): void
    {
        if ($limitBytes === null || $limitBytes <= 0 || $currentBytes <= $previousBytes) {
            return;
        }

        $percentUsed = ($limitBytes > 0) ? (($currentBytes / $limitBytes) * 100) : 0;
        $notificationTitle = 'Storage alert';
        $emailTemplate = 'storage-warning';
        $targetUrl = '/dashboard#storage';

        if ($percentUsed >= 100 && $previousBytes < $limitBytes) {
            $message = sprintf('Your storage is full at %s. Please delete files or upgrade to Premium to continue uploading.', number_format($percentUsed, 0));
            $this->notifyUserWithEmail($userId, 'Storage full', $message, 'Your storage is full', $emailTemplate, ['message' => $message], 'hosting', 'hard-drive', $targetUrl);
            return;
        }

        if ($percentUsed >= 95 && $previousBytes < ($limitBytes * 0.95)) {
            $message = sprintf('Your storage usage is at %s. You are nearing your limit and should remove files or upgrade soon.', number_format($percentUsed, 0));
            $this->notifyUserWithEmail($userId, 'Storage almost full', $message, 'Storage usage alert', $emailTemplate, ['message' => $message], 'hosting', 'hard-drive', $targetUrl);
            return;
        }

        if ($percentUsed >= 80 && $previousBytes < ($limitBytes * 0.80)) {
            $message = sprintf('Your storage is %s full. Keep an eye on your draft files as you continue working.', number_format($percentUsed, 0));
            $this->createNotification($userId, 'Storage usage warning', $message, 'hosting', 'hard-drive', $targetUrl, false);
        }
    }

    private function shouldSendEmail(): bool
    {
        return ($this->settings['smtp_enabled'] ?? '0') === '1'
            && ($this->settings['email_notifications_enabled'] ?? '0') === '1';
    }

    private function renderEmailTemplate(string $templateName, array $data = []): string
    {
        $templatePath = APP_PATH . '/Views/emails/' . $templateName . '.php';
        if (!is_file($templatePath)) {
            throw new \RuntimeException("Email template {$templateName} not found.");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;

        return ob_get_clean();
    }
}
