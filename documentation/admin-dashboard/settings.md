# Admin Dashboard: Settings

## Purpose

The Settings module exposes application configuration for branding, appearance, plans, SMTP, maintenance, security, uploads, backups, and advanced customization.

## Primary Controller

- `app/Controllers/AdminController.php`
- `app/Models/Setting.php`

## Key actions

- `settings()` renders the tabbed settings form.
- `saveSettings()` persists settings values and uploaded branding files.
- `previewMaintenance()` renders the maintenance page using current maintenance settings.
- `sendTestEmail()` validates SMTP settings by sending a test message.

## Settings structure

`AdminController::allowedSettings()` defines the permitted keys and tab grouping.
`AdminController::checkboxSettings()` defines boolean flags.

Settings include:
- General application metadata
- Appearance and branding
- Homepage content
- Footer configuration
- Pricing and PayChangu keys
- SMTP and email settings
- Security controls like `recaptcha_enabled` and `maintenance_mode`
- Upload and storage limits
- Advanced raw HTML head code

## File uploads

- Branding uploads are handled by `processBrandingUploads()`.
- Allowed image formats: `png`, `jpg`, `jpeg`, `gif`, `webp`, `bmp`, `svg`, `ico`.
- Uploaded branding files are saved under `public/uploads/branding`.

## Notes

- Settings are saved through `Setting::saveMany()`.
- `previewMaintenance()` supports an admin-only preview path without enabling live maintenance mode.
- `sendTestEmail()` uses `MailerService` to validate email connectivity.
