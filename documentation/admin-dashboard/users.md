# Admin Dashboard: Users

## Purpose

The Admin Users module provides platform administrators with account lifecycle controls, plan management, and role enforcement.

## Primary Controller

- `app/Controllers/AdminController.php`

## Key Actions

- `users()` renders the user management table.
- `editUser()` loads an editable user record.
- `updateUser()` validates profile fields and updates user metadata.
- `changeUserPlan()` switches a user between Free and Premium plans.
- `resetRole()` returns a user to the default `user` role.
- `deleteUser()` removes a user account and cascades dependent data.

## Behavior and business rules

- Admin-only access is enforced by `AdminController::adminOnly()`.
- CSRF protection is required for all POST actions.
- Profile updates validate fields with `App\Core\Validator` and custom rules for usernames and role/status selection.
- Users cannot change their own plan or delete their own account from the admin console.
- Premium plan changes expire existing active subscriptions and insert a new subscription row.
- Role changes are restricted to explicit allowable role keys.

## Data model

- User records are stored in `users`.
- Subscription changes are stored in `subscriptions`.
- Payment, website, and notification state is derived by joined queries in `AdminRepository`.

## Notes

- `AdminRepository::users()` resolves the current plan by combining explicit roles with the latest active subscription.
- Plan transitions trigger a notification email via `NotificationManager`.
