<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use DateTimeImmutable;
use PDO;

/**
 * Handles login state, logout, and remember-me token persistence.
 */
class AuthService
{
    private const REMEMBER_COOKIE = 'turbohost_remember';

    /**
     * Log in a user for the current session.
     */
    public static function login(array $user, bool $remember = false): void
    {
        Session::regenerate();
        Session::put('user_id', (int) $user['id']);
        Session::put('user_name', $user['fullname']);
        Session::put('user_role', $user['role']);

        if ($remember) {
            self::createRememberToken((int) $user['id']);
        }
    }

    /**
     * Log the active user out and remove remember-me state.
     */
    public static function logout(): void
    {
        self::deleteRememberTokenFromCookie();
        Session::forget('user_id');
        Session::forget('user_name');
        Session::forget('user_role');
        Session::regenerate();
    }

    /**
     * Return the current authenticated user ID.
     */
    public static function id(): ?int
    {
        $userId = Session::get('user_id');

        return $userId ? (int) $userId : null;
    }

    /**
     * Return true when a user is authenticated.
     */
    public static function check(): bool
    {
        return self::id() !== null;
    }

    /**
     * Return true when the current user has an administrator role.
     */
    public static function isAdmin(): bool
    {
        $sessionRole = Session::get('user_role');
        if (in_array($sessionRole, ['admin', 'super_admin'], true)) {
            return true;
        }

        $userId = self::id();
        if ($userId === null) {
            return false;
        }

        $database = Database::connection();
        $statement = $database->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $databaseRole = $statement->fetchColumn();

        return in_array((string) $databaseRole, ['admin', 'super_admin'], true);
    }

    /**
     * Restore a session from a valid remember-me cookie.
     */
    public static function loginFromRememberCookie(): void
    {
        if (self::check() || empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }

        [$selector, $token] = array_pad(explode(':', $_COOKIE[self::REMEMBER_COOKIE], 2), 2, '');

        if ($selector === '' || $token === '') {
            self::clearRememberCookie();
            return;
        }

        $database = Database::connection();
        $statement = $database->prepare(
            'SELECT rt.token_hash, u.* FROM remember_tokens rt
             INNER JOIN users u ON u.id = rt.user_id
             WHERE rt.selector = :selector AND rt.expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['selector' => $selector]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$record || !hash_equals($record['token_hash'], hash('sha256', $token))) {
            self::clearRememberCookie();
            return;
        }

        self::login($record, true);
    }

    /**
     * Create and store a hashed remember-me token.
     */
    private static function createRememberToken(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

        $database = Database::connection();
        $statement = $database->prepare(
            'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
             VALUES (:user_id, :selector, :token_hash, :expires_at)'
        );
        $statement->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        setcookie(self::REMEMBER_COOKIE, $selector . ':' . $token, [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }

    /**
     * Delete the remember-me token represented by the current cookie.
     */
    private static function deleteRememberTokenFromCookie(): void
    {
        if (!empty($_COOKIE[self::REMEMBER_COOKIE])) {
            [$selector] = array_pad(explode(':', $_COOKIE[self::REMEMBER_COOKIE], 2), 2, '');

            if ($selector !== '') {
                $database = Database::connection();
                $statement = $database->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
                $statement->execute(['selector' => $selector]);
            }
        }

        self::clearRememberCookie();
    }

    /**
     * Expire the remember-me browser cookie.
     */
    private static function clearRememberCookie(): void
    {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    }
}
