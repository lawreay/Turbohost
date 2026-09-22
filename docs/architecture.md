# Architecture guide

## 1. High-level structure

This project is a custom PHP MVC application that behaves like a lightweight hosting and CMS platform.

Key layers:

- Controller layer: receives HTTP requests and decides what to do
- Service layer: contains business logic and orchestration
- Model layer: data access and persistence
- View layer: page templates and presentation
- Core framework layer: router, session, validation, database, and bootstrap helpers

## 2. Core framework pieces

### App bootstrap

`index.php` starts the application, and `app/Core/App.php` performs the init flow.

The bootstrap does the following:

- sends security headers
- loads application config
- loads route definitions
- starts the PHP session
- restores remember-me login state
- ensures the sitemap file exists
- dispatches the request to the router

### Router

The router is responsible for matching request paths and methods to controller actions.

Routes are defined in `app/Config/routes.php` and are grouped by HTTP method.

### Session and auth

Session handling is centralized in `app/Core/Session.php` and related auth services.

Authentication helpers validate:

- session login state
- remember-me cookies
- user roles and permissions logic
- current authenticated user session

## 3. Model pattern

Each entity type is represented by a model class in `app/Models`.

Examples include:

- `User`
- `Website`
- `Payment`
- `Notification`
- `LegalPolicy`

These classes manage CRUD queries and domain-specific database logic but do not contain request handling.

## 4. Service layer

The service layer contains the business rules.

Examples:

- `AuthService`
- `ProjectStorageService`
- `PublishingService`
- `SitemapGenerator`
- `NotificationManager`
- `PlanService`
- `PublicSiteUrlService`

This layer is used when work involves validation, file actions, permissions, notifications, publishing, or domain logic.

## 5. Storage architecture

The project uses a two-part storage model:

### Draft storage

Drafts are stored under:

- `storage/projects/{userId}/{slug}`

This area is editable by the project owner and is not public.

### Public storage

Published content is copied to:

- `public/sites/{projectId}/{slug}`

This folder is the public output of the hosted website. Static sites are published here with filtered file types. WordPress projects are also published here with WordPress-specific access rules.

## 6. Publishing flow

The publishing flow is implemented in `app/Services/PublishingService.php`.

It works like this:

1. Locate the draft project folder.
2. Verify the project is valid for publishing.
3. Replace the target public directory.
4. Copy only allowed files into the public site.
5. Write the WordPress `.htaccess` settings where needed.
6. Return the public URL for the live site.

This keeps the public site and the editable project storage separate.

## 7. Website lifecycle

Website creation is handled by `WebsiteController::store()`.

The flow is:

1. Validate user inputs and CSRF token
2. Check plan limits and access
3. Generate an available slug
4. Create a `websites` record in the database
5. Prepare the project directory
6. Generate starter content or WordPress package content
7. Update storage usage
8. Notify the user

## 8. File management and editor

Project editing is separated into:

- file management routes for upload/create/delete/move/export
- text editing routes for code-like files

The file manager works inside the project directory and prevents escapes outside the valid project root.

## 9. WordPress support

WordPress provisioning is handled by `ProjectStorageService::installWordPress()`.

This implementation:

- looks up the configured package path
- validates the archive checksum
- rejects suspicious ZIP entries
- extracts into a temporary directory
- validates required WordPress files
- writes a generated `wp-config.php`
- finalizes the install into the project directory

The public site is then hosted with rules that allow PHP execution while still denying sensitive files.

## 10. Security model

This project includes several security patterns:

- CSRF validation for state-changing requests
- request input validation
- path normalization before file operations
- session security headers
- no direct public access to mutable draft storage
- separate editable and published paths
- WordPress safety checks for archive content

## 11. Admin and user separation

The app separates:

- public pages
- authenticated user dashboard
- administrative functions

This is done through route grouping and controller responsibilities.

## 12. Extension points

If you want to add new features, the cleanest path is:

- add a controller action under `app/Controllers`
- add or reuse a service in `app/Services`
- add SQL access in `app/Models`
- add views in `app/Views`
- register the route in `app/Config/routes.php`

This keeps domain logic and HTTP logic decoupled.
