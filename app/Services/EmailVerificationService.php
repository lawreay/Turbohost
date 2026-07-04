<?php

namespace App\Services;

use DateTimeImmutable;
use PDO;

/**
 * Manages email verification tokens for user accounts.
 */
class EmailVerificationService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * Create and store a verification token for the selected user.
     */
    public function createToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $statement = $this->database->prepare(
            'INSERT INTO email_verifications (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)'
        );
        $statement->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Verify a token and mark the user email as confirmed when valid.
     *
     * @return int|false The verified user ID, or false if verification failed.
     */
    public function verifyToken(string $token)
    {
        $tokenHash = hash('sha256', $token);

        $statement = $this->database->prepare(
            'SELECT id, user_id FROM email_verifications
             WHERE token_hash = :token_hash
             AND used_at IS NULL
             AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $verification = $statement->fetch();

        if (!$verification) {
            return false;
        }

        $this->database->beginTransaction();

        $updateUser = $this->database->prepare(
            "UPDATE users SET email_verified = TRUE, account_status = 'active' WHERE id = :user_id"
        );
        $updateUser->execute(['user_id' => $verification['user_id']]);

        $updateToken = $this->database->prepare('UPDATE email_verifications SET used_at = NOW() WHERE id = :id');
        $updateToken->execute(['id' => $verification['id']]);

        $this->database->commit();

        return (int) $verification['user_id'];
    }
}
