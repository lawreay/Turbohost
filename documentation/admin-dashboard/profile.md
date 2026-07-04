# Admin Dashboard: Profile

## Purpose

The Profile module covers user profile editing and self-service account maintenance for signed-in users.

## Primary Controllers

- `app/Controllers/DashboardController.php`
- `app/Controllers/AdminController.php` (for admin profile view)

## Key actions

- `DashboardController::profile()` renders the profile page or admin profile editor.
- `DashboardController::updateProfile()` updates name, phone, country, bio, and optional avatar uploads.
- `DashboardController::changePassword()` validates the current password and updates the password hash.
- `DashboardController::changeEmail()` begins an email change flow requiring current password verification.

## File handling

- Avatar uploads are saved to `public/uploads/avatars`.
- Uploaded avatars are converted to WebP when possible.
- Allowed avatar extensions are `png`, `jpg`, `jpeg`, `gif`, and `bmp`.
- The maximum avatar size is 2MB.

## Notes

- Authenticated users are required for profile actions.
- The profile view is rendered through either `layouts/dashboard` or `layouts/admin` based on admin status.
- Profile updates cascade session display values for the signed-in user.
