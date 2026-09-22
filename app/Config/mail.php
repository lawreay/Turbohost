<?php
/**
 * Mail transport configuration.
 */

require_once __DIR__ . '/constants.php';

return [
    'from_name' => (string) env('MAIL_FROM_NAME', env('APP_NAME', 'TurboHostMw')),
    'from_address' => (string) env('MAIL_FROM_ADDRESS', 'no-reply@turbohostmw.com'),
    'host' => (string) env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => (int) env('MAIL_PORT', 587),
    'username' => (string) env('MAIL_USERNAME', ''),
    'password' => (string) env('MAIL_PASSWORD', ''),
    'encryption' => (string) env('MAIL_ENCRYPTION', 'tls'),
    'admin_login_notification_address' => (string) env('ADMIN_LOGIN_NOTIFICATION_EMAIL', ''),
    'admin_login_notification_message' => (string) env(
        'ADMIN_LOGIN_NOTIFICATION_MESSAGE',
        'An administrator has signed in to the TurboHostMw site.'
    ),
];
