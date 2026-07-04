<?php

namespace App\Core;

/**
 * Safe wrapper around PHP session state and flash messages.
 */
class Session
{
    /**
     * Start the session with secure defaults.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);

        session_start();
    }

    /**
     * Store a value in the session.
     */
    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Read a value from the session.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove a value from the session.
     */
    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Regenerate the session ID after authentication changes.
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Store a one-request flash message.
     */
    public static function flash(string $key, string $message): void
    {
        self::put('_flash_' . $key, $message);
    }

    /**
     * Read and remove a flash message.
     */
    public static function pullFlash(string $key): ?string
    {
        $flashKey = '_flash_' . $key;
        $message = self::get($flashKey);
        self::forget($flashKey);

        return $message;
    }
}
