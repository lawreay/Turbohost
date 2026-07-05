# TurboHostMw

TurboHostMw is a custom PHP-based website hosting and publishing platform built with a lightweight MVC architecture.

## Features

- User registration, login, email verification, and two-factor authentication
- Client dashboard for website project management
- Draft website file editing and file manager support
- Static site publishing to `public/sites/{projectId}/{slug}/`
- PayChangu Premium checkout and payment verification
- Admin dashboard for user, website, media, payment, notification, report, legal, and settings management
- Dynamic XML sitemap generation for marketing pages and published sites
- Maintenance mode with bypass token support

## Project Structure

- `app/` — core application classes, controllers, services, and models
- `app/Core/` — base framework components: router, controller, model, database, CSRF, validation, view engine
- `app/Controllers/` — HTTP request handlers for public site, auth, dashboard, admin, and payment workflows
- `app/Services/` — reusable business logic for authentication, publishing, notifications, storage, payments, sitemap, and URLs
- `app/Models/` — data access objects and repositories for users, websites, subscriptions, payments, reports, notifications, and legal policies
- `app/Config/` — runtime configuration and route definitions
- `public/` — web server document root and public assets
- `storage/` — generated project storage, backups, exports, and logs
- `documentation/` — engineering documentation and admin module docs

## Installation

1. Install PHP and required extensions: PDO, cURL, fileinfo, and optionally GD or Imagick for image conversion.
2. Install Composer dependencies:

   ```bash
   composer install
   ```

3. Create a writable `.env` file using the existing `.env` sample values.
4. Configure database credentials in `app/Config/database.php` or via environment variables.
5. Import the schema from `database/schema.sql` into your MySQL/MariaDB instance.
6. Ensure `public/`, `storage/`, and `storage/projects/` are writable by the web server.

## Running

Set your web server document root to `public/`, or use the PHP built-in server for local development:

```bash
php -S localhost:8000 -t public
```

Then open `http://localhost:8000` in the browser.

## Documentation

Additional documentation is available in the `documentation/` folder, including admin dashboard module guides under `documentation/admin-dashboard/`.

## Contributing

Contributions are welcome. Please follow these guidelines:

- Keep feature-specific logic in `app/Services/`
- Use controllers for request validation, authentication, and view rendering only
- Add or update `documentation/` for any architectural or workflow changes
- Preserve security controls like CSRF validation, path normalization, and authenticated routing

## License

Add licensing information here if the project is open source.
