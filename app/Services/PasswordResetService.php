<?php

namespace App\Services;

use DateTimeImmutable;
use PDO;

/**
 * Manages password reset token creation and consumption.
 */
class PasswordResetService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * Create and store a reset token for the selected user.
     */
    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

        $statement = $this->database->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)'
        );
        $statement->execute([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Return the matching active reset token row.
     */
    public function findValidToken(string $token): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, user_id FROM password_resets
             WHERE token_hash = :token_hash
             AND used_at IS NULL
             AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['token_hash' => hash('sha256', $token)]);
        $reset = $statement->fetch();

        return $reset ?: null;
    }

    /**
     * Mark a password reset token as used.
     */
    public function markUsed(int $resetId): void
    {
        $statement = $this->database->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $resetId]);
    }
}
