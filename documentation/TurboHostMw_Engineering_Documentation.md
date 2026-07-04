# TurboHostMw Engineering Documentation

## Overview

TurboHostMw is a custom PHP MVC hosting platform for building, managing, and publishing static websites. The application supports:
- User registration, login, email verification, and two-factor authentication.
- Draft website creation, editor-based file management, and publishing to static public folders.
- PayChangu-based payment checkout for Premium upgrades.
- Admin dashboard controls for users, websites, media, payments, reports, notifications, settings, and legal policy management.
- Dynamic sitemap generation for public pages and published websites.

This document is intended for developers, maintainers, and operations teams.

## Codebase Structure

Key top-level directories:
- `app/`: core application controllers, services, models, and bootstrap classes.
- `app/Core/`: framework layer for routing, controllers, models, sessions, CSRF, database, and view rendering.
- `app/Controllers/`: request handlers for marketing, authentication, dashboard, admin, website publishing, file management, payments, and legal workflows.
- `app/Services/`: reusable business logic for authentication, notifications, publishing, storage, payments, sitemap generation, and URL construction.
- `app/Models/`: data access objects and repositories for users, websites, payments, files, policies, settings, and analytics.
- `app/Config/`: runtime configuration including environment-aware `app.php` and route definitions in `routes.php`.
- `public/`: web server document root and public assets.
- `storage/`: application-generated files, project drafts, backups, and exports.
- `documentation/`: this engineered reference documentation.

## Boot Sequence

The application bootstraps in `app/Core/App.php`.

1. Load runtime configuration from `app/Config/app.php`.
2. Load routing definitions from `app/Config/routes.php`.
3. Start session state with `App\Core\Session::start()`.
4. Attempt remember-me login from `App\Services\AuthService::loginFromRememberCookie()`.
5. Ensure `public/sitemap.xml` exists using `App\Services\SitemapGenerator::ensureFileExists()`.
6. Dispatch the current HTTP request through `App\Core\Router::dispatch()`.

## Routing and HTTP Dispatch

`app/Core/Router.php` resolves incoming requests by:
- Normalizing request path and stripping any configured base URL.
- Evaluating maintenance mode and rendering a maintenance page when enabled.
- Matching registered routes from `app/Config/routes.php`.
- Instantiating controller classes and calling controller actions.

Routes are organized by HTTP method (`GET` and `POST`). Notable routes include:
- `/login`, `/register`, `/dashboard`, `/dashboard/websites`, `/admin`, `/admin/settings`, `/admin/legal`.
- `/sitemap.xml` served by `App\Controllers\SitemapController`.
- `/payments/paychangu/callback` and `/payments/paychangu/return` for payment verification.
- `/legal/accept` for published policy acceptance flow.

## Core MVC Contracts

### Controller Layer

`app/Core/Controller.php` provides shared helpers:
- `view()` renders templates through `App\Core\View`.
- `redirect()` and `redirectTo()` manage HTTP redirects.
- `input()` reads request data safely.

Controllers follow a lightweight action-based pattern.

### Model Layer

`app/Core/Model.php` provides:
- A shared PDO connection via `App\Core\Database::connection()`.
- A `query()` helper for prepared statements.
- A generic `find()` implementation for primary-key lookup.

Models encapsulate SQL and persistence logic with table-specific implementations.

### Configuration

`app/Config/app.php` defines runtime configuration and builds a `base_url` dynamically from the incoming request host. It also provides centralized application values such as:
- `name`, `environment`, `debug`, `timezone`
- `paths` for root, app, public, and storage directories
- `storage` limits for free and premium plans

## Key Services

### AuthService

Located at `app/Services/AuthService.php`, it handles:
- Session login and logout.
- Remember-me cookie token creation and validation.
- Admin role enforcement via `isAdmin()`.

Security notes:
- Remember tokens are stored hashed in `remember_tokens`.
- Cookies use `HttpOnly`, `SameSite=Lax`, and secure flag when HTTPS is detected.

### SitemapGenerator

Implemented in `app/Services/SitemapGenerator.php`.
- Generates XML for public marketing pages and published website projects.
- Writes a static `public/sitemap.xml` file.
- Called during application startup and after state changes such as publishing, unpublishing, suspension, or deletion.

### ProjectStorageService

Located in `app/Services/ProjectStorageService.php`.
- Manages draft project directories under `storage/projects/{userId}/{slug}`.
- Normalizes and validates user-provided relative paths.
- Creates starter `index.html` pages and manages file/folder operations.

### PublicSiteUrlService

Located in `app/Services/PublicSiteUrlService.php`.
- Builds canonical URLs for published sites under `sites/{projectId}/{slug}/`.
- Ensures consistent public URL generation for both admin notifications and sitemap entries.

### PayChanguService

Located in `app/Services/PayChanguService.php`.
- Communicates with PayChangu using cURL.
- Verifies payment response payloads and status codes.
- Uses certificate bundle detection for secure TLS verification.

### NotificationManager

Located in `app/Services/NotificationManager.php`.
- Wraps `NotificationService` and provides broadcast and user notification flows.
- Sends email-backed notifications for user signup, policy updates, subscription changes, and admin broadcasts.

## Data Model and Database

Primary tables include:
- `users`
- `websites`
- `files`
- `subscriptions`
- `payments`
- `notifications`
- `analytics`
- `reports`
- `legal_policies`, `legal_policy_versions`, `policy_audit_logs`, `user_policy_acceptances`
- `remember_tokens`

Important relationships:
- `websites.user_id` -> `users.id`
- `files.website_id` -> `websites.id`
- `payments.user_id` -> `users.id`
- `subscriptions.user_id` -> `users.id`
- `reports.website_id` -> `websites.id`

The `Website` model provides published site lookup and plan determination logic used across dashboard and sitemap generation.

## Admin Dashboard Documentation

The admin area is responsible for high-level platform operations. Detailed module docs are available in `documentation/admin-dashboard/`:

- `users.md`
- `websites.md`
- `media.md`
- `payments.md`
- `reports.md`
- `notifications.md`
- `profile.md`
- `legal.md`
- `settings.md`

## Security and Governance

### Authentication and Authorization

- All admin routes use `AuthService::check()` and `AuthService::isAdmin()`.
- `AdminController::adminOnly()` and `LegalController::adminOnly()` enforce administrator access.
- Public dashboard and website management routes require authentication.

### CSRF Protection

- Actions that mutate state validate CSRF tokens using `App\Core\Csrf::validate()`.
- Failed CSRF checks redirect users to the appropriate form with an error message.

### Input Validation

- `App\Core\Validator` validates request payloads for string length, email formats, and required fields.
- Controllers add domain-specific validation, e.g. username patterns and slug uniqueness.

### File and Path Safety

- `ProjectStorageService::normalizeRelativePath()` rejects `.` and `..` and strips invalid characters.
- Uploaded media files are checked against allowed extensions and maximum file size limits.
- Media uploads support image conversion to WebP when PHP image extensions are available.

### Payment and Data Integrity

- `PaymentController` verifies PayChangu transactions before activating Premium subscriptions.
- The verification path confirms `tx_ref`, `currency`, `amount`, and response status.
- Payment records are stored in `payments` with `pending`, `paid`, and `failed` statuses.

### Maintenance Mode

- `Router::dispatch()` checks `maintenance_mode` and displays a maintenance page for non-admin visitors.
- A bypass token can be stored in settings and passed via query string for authorized access.

## Failure Modes and Resilience

The application uses defensive error handling in several key paths:
- Sitemap generation failures are caught and logged implicitly without preventing app startup.
- Publish/unpublish/suspension actions regenerate sitemap files, but exceptions are suppressed so user workflow continues.
- Project creation cleans up partially created directories and database state on failure.
- Notification failures during broadcast or legal updates are ignored to preserve the main workflow.

## Extension Guidelines

To extend the platform:
1. Add a new route in `app/Config/routes.php`.
2. Implement controller logic in `app/Controllers/`.
3. Add persistence methods to `app/Models/` or create a new model.
4. Encapsulate business logic in `app/Services/` when behavior is reusable.
5. Add template views under `app/Views/` and update layouts as needed.

Use the existing naming conventions and keep business rules within services rather than directly in controllers.

## Operational Notes

- `base_url` is derived at runtime and can be overridden via environment settings.
- `public/sitemap.xml` is generated automatically and should be writable by the PHP process.
- Draft website files are stored outside the web root under `storage/projects`.
- Published website output is served under `public/sites/{projectId}/{slug}/`.

## Maintaining Documentation

Keep this documentation aligned with code changes by updating:
- route additions and path changes in `app/Config/routes.php`
- admin workflows in `app/Controllers/AdminController.php`
- published site URL rules in `app/Services/PublicSiteUrlService.php`
- sitemap generation behavior in `app/Services/SitemapGenerator.php`

For feature-specific updates, add or extend module docs inside `documentation/admin-dashboard/`.
