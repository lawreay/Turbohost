<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Persists PayChangu checkout attempts and applies subscription upgrades.
 */
class Payment extends Model
{
    protected string $table = 'payments';

    /**
     * Create a pending payment record before redirecting to PayChangu.
     */
    public function createPending(int $userId, float $amount, string $currency, string $txRef): int
    {
        $statement = $this->query(
            "INSERT INTO payments (user_id, amount, currency, payment_method, transaction_id, tx_ref, payment_status)
             VALUES (:user_id, :amount, :currency, 'PayChangu', :transaction_id, :tx_ref, 'pending')",
            [
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency,
                'transaction_id' => $txRef,
                'tx_ref' => $txRef,
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    /**
     * Find a payment by its unique provider transaction reference.
     */
    public function findByReference(string $txRef): ?array
    {
        $statement = $this->query('SELECT * FROM payments WHERE tx_ref = :tx_ref LIMIT 1', ['tx_ref' => $txRef]);
        $payment = $statement->fetch(PDO::FETCH_ASSOC);

        return $payment ?: null;
    }

    /**
     * Mark a verified transaction as paid and store the provider response.
     */
    public function markPaid(int $paymentId, string $providerReference, array $payload): void
    {
        $this->query(
            "UPDATE payments
             SET payment_status = 'paid',
                 transaction_id = :transaction_id,
                 provider_response = :provider_response,
                 verified_at = NOW()
             WHERE id = :id",
            [
                'id' => $paymentId,
                'transaction_id' => $providerReference,
                'provider_response' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    /**
     * Mark a transaction as failed and keep the provider response for review.
     */
    public function markFailed(int $paymentId, array $payload = []): void
    {
        $this->query(
            "UPDATE payments
             SET payment_status = 'failed',
                 provider_response = :provider_response,
                 verified_at = NOW()
             WHERE id = :id AND payment_status <> 'paid'",
            [
                'id' => $paymentId,
                'provider_response' => $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    /**
     * Create or extend a premium subscription after a verified payment.
     */
    public function activatePremiumSubscription(int $userId, float $amount): void
    {
        $this->database->beginTransaction();

        $this->query(
            "UPDATE subscriptions
             SET status = 'expired'
             WHERE user_id = :user_id AND status = 'active'",
            ['user_id' => $userId]
        );

        $this->query(
            "INSERT INTO subscriptions (user_id, plan, amount, start_date, end_date, status)
             VALUES (:user_id, 'premium', :amount, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 'active')",
            [
                'user_id' => $userId,
                'amount' => $amount,
            ]
        );

        $this->query(
            "UPDATE users
             SET role = CASE
                 WHEN role IN ('admin', 'super_admin', 'moderator') THEN role
                 ELSE 'premium'
             END
             WHERE id = :id",
            ['id' => $userId]
        );

        $this->database->commit();
    }
}
