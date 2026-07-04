<?php
/**
 * Shared application constants and lightweight environment loading.
 */

if (!function_exists('env')) {
    /**
     * Read an environment value with a safe fallback.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false || $value === null || $value === '' ? $default : $value;
    }
}

if (!function_exists('load_env_file')) {
    /**
     * Load simple KEY=value pairs from the project .env file.
     */
    function load_env_file(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

            if ($key !== '' && getenv($key) === false) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
}

define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
define('STORAGE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
define('UPLOAD_PATH', STORAGE_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('WEBSITE_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'websites');

define('BYTES_PER_MB', 1024 * 1024);
define('FREE_PLAN_STORAGE_LIMIT', 100 * BYTES_PER_MB);
define('DEFAULT_UPLOAD_FILE_LIMIT', 10 * BYTES_PER_MB);

load_env_file(ROOT_PATH . DIRECTORY_SEPARATOR . '.env');
