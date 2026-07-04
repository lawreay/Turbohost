<?php

namespace App\Services;

use DateTimeImmutable;
use PDO;

/**
 * Manages two-factor authentication codes.
 */
class TwoFactorService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * Create a one-time 2FA code for the user.
     */
    public function createCode(int $userId): string
    {
        $code = random_int(100000, 999999);
        $expiresAt = (new DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s');

        $statement = $this->database->prepare(
            'INSERT INTO two_factor_codes (user_id, code_hash, expires_at) VALUES (:user_id, :code_hash, :expires_at)'
        );
        $statement->execute([
            'user_id' => $userId,
            'code_hash' => hash('sha256', (string) $code),
            'expires_at' => $expiresAt,
        ]);

        return (string) $code;
    }

    /**
     * Verify the active 2FA code and mark it used.
     */
    public function verifyCode(int $userId, string $code): bool
    {
        $statement = $this->database->prepare(
            'SELECT id, code_hash FROM two_factor_codes
             WHERE user_id = :user_id
             AND used_at IS NULL
             AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$record || !hash_equals($record['code_hash'], hash('sha256', $code))) {
            return false;
        }

        $markUsed = $this->database->prepare('UPDATE two_factor_codes SET used_at = NOW() WHERE id = :id');
        $markUsed->execute(['id' => $record['id']]);

        return true;
    }
}
