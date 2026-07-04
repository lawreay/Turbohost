# Admin Dashboard: Media

## Purpose

The Media module provides platform administrators with an upload manager for shared assets and branding files.

## Primary Controller

- `app/Controllers/AdminController.php`

## Key Actions

- `media()` renders the media listing.
- `uploadMedia()` saves uploaded assets to `public/uploads`.
- `deleteMedia()` deletes media files.
- `renameMedia()` renames files and validates allowed extensions.

## File handling

- Uploads are stored in `public/uploads`.
- Branding uploads are stored under `public/uploads/branding`.
- Allowed file extensions are defined in `AdminController::allowedUploadExtensions()`.
- Image uploads may be converted to WebP using either Imagick or GD.

## Security and validation

- CSRF protection is enforced for all state-changing actions.
- File size limits use `storage.default_upload_file_bytes` from configuration.
- Filenames are sanitized before storage and collisions are rejected.
- Uploaded images must be one of: `png`, `jpg`, `jpeg`, `gif`, `svg`, `webp`, `ico`, `bmp`, `txt`, `pdf`, `zip`, `css`, `js`.

## Notes

- `processBrandingUploads()` is used during settings save to move uploaded branding assets into a managed subfolder.
- Media management is primarily a platform-level convenience for shared static assets.
