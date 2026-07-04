<?php

namespace App\Core;

/**
 * Generates and validates CSRF tokens for forms and AJAX requests.
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Return the active token or create a new one.
     */
    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Return a hidden input field for HTML forms.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    /**
     * Validate a submitted CSRF token.
     */
    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get(self::SESSION_KEY);

        return is_string($token)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $token);
    }
}
