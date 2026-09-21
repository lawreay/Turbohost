<?php
/**
 * Application runtime configuration.
 */

require_once __DIR__ . '/constants.php';

$timezone = (string) env('APP_TIMEZONE', 'Africa/Blantyre');
date_default_timezone_set($timezone);

$configuredBaseUrl = rtrim((string) env('APP_URL', ''), '/');
$requestHost = $_SERVER['HTTP_HOST'] ?? '';

if ($requestHost !== '') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on');
    $scheme = $isHttps ? 'https' : 'http';
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/(public/)?index\.php$#', '', $scriptName) ?: '';
    $requestBaseUrl = rtrim($scheme . '://' . $requestHost . $basePath, '/');
    $configuredHost = (string) parse_url($configuredBaseUrl, PHP_URL_HOST);

    if ($configuredBaseUrl === '' || ($configuredHost !== '' && !hash_equals($configuredHost, $requestHost))) {
        $configuredBaseUrl = $requestBaseUrl;
    }
}

return [
    'name' => (string) env('APP_NAME', 'TurboHostMw'),
    'environment' => (string) env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => $configuredBaseUrl !== '' ? $configuredBaseUrl : 'http://localhost/TurboHostMw/public',
    'timezone' => $timezone,
    'paths' => [
        'root' => ROOT_PATH,
        'app' => APP_PATH,
        'public' => PUBLIC_PATH,
        'storage' => STORAGE_PATH,
        'updates' => STORAGE_PATH . DIRECTORY_SEPARATOR . 'updates',
        'uploads' => UPLOAD_PATH,
        'website_uploads' => WEBSITE_UPLOAD_PATH,
    ],
    'storage' => [
        'free_plan_bytes' => FREE_PLAN_STORAGE_LIMIT,
        'premium_plan_bytes' => null,
        'default_upload_file_bytes' => DEFAULT_UPLOAD_FILE_LIMIT,
    ],
];
