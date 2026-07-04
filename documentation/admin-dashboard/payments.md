# Admin Dashboard: Payments

## Purpose

The Payments module supports premium upgrades through PayChangu and allows administrators to review payment records.

## Primary Controllers and Services

- `app/Controllers/PaymentController.php`
- `app/Services/PayChanguService.php`
- `app/Models/Payment.php`

## Payment workflow

1. User initiates Premium checkout via `PaymentController::startPremiumCheckout()`.
2. A local pending payment record is inserted using `Payment::createPending()`.
3. The application calls `PayChanguService::initiatePayment()` to start a hosted checkout.
4. PayChangu redirects back to `/payments/paychangu/callback` or `/payments/paychangu/return`.
5. `PaymentController::verifyRedirect()` validates the payment and, if successful, marks the record as paid and activates Premium.

## Validation and security

- `PayChanguService` requires PHP cURL and TLS verification.
- Secret key configuration is required in `settings.paychangu_secret_key`.
- Redirects validate `tx_ref`, `currency`, and `amount` values using secure comparisons.
- The `roleAfterUpgrade()` method preserves admin/moderator roles and only changes standard users to `premium`.

## Admin record access

- Admin payment reporting uses `AdminRepository::payments()` to join `payments` with user details.
- Payment records include `transaction_id`, `tx_ref`, `payment_status`, and date information.

## Notes

- The application supports a fallback `premium_plan_price` when `premium_plan_amount` is not set.
- The public-facing currency is normalized to uppercase.
