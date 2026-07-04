# Admin Dashboard: Reports

## Purpose

The Reports module allows administrators to review abuse and moderation tickets submitted for website projects.

## Primary Controller

- `app/Controllers/AdminController.php`
- `app/Models/AdminRepository.php`

## Data model

- Reports are stored in the `reports` table.
- Each report may include a reference to `website_id` and the reporting user's ID.

## Admin actions

- `reports()` displays report rows with related website and reporter details.
- Reports are rendered with status values such as `pending`, `reviewed`, and `resolved`.

## Notes

- The current implementation provides read-only review features from the admin dashboard.
- Additional moderation workflows may be added by extending `AdminController` and `AdminRepository`.
