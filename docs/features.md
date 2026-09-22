# Feature guide

## Public website and marketing pages

The public front end is designed for a hosted website platform with marketing pages like:

- home
- features
- pricing
- about
- contact
- FAQ
- legal pages

These routes are handled by marketing and legal controllers and use the public-facing layout templates.

## Authentication

The authentication flow covers:

- user registration
- login/logout
- forgot password
- reset password
- email verification
- two-factor verification
- remember-me login persistence

The core login flow is orchestrated by the auth controllers and services.

## Dashboard and project management

Once a user is logged in, the dashboard shows:

- site list
- storage usage
- active plan state
- notifications
- account info and settings

The user can create new website projects and manage drafts from the dashboard.

## Website drafting

Users can create projects with different starter templates. The project is created inside an isolated storage folder and supports both static sites and WordPress installs.

Typical flow:

- create site record
- create project folder
- generate starter files
- save initial metadata
- show preview/edit links

## File manager

The file manager is the core project editor for content management.

It supports:

- folder creation
- file creation
- upload
- delete
- rename/move operations
- ZIP import/export
- file download

These actions ensure the project remains within its safe root directory.

## Editing and preview

Users can edit project content in the built-in editor.

The editor supports text files and is useful for site content, HTML, CSS, JSON, and other editable project files.

## Publishing

Publishing takes the draft project and copies it into the public sites directory.

This gives a live URL while keeping editable draft content separated from public output.

## WordPress

The WordPress support allows a project to be provisioned with a WordPress archive, create a generated config file, and then publish as an executable site.

The WordPress flow includes:

- ZIP validation
- checksum verification
- required-file checks
- temporary extraction
- config generation
- live publishing

## Notifications

The platform keeps users informed with notifications about things like:

- billing changes
- website creation
- account updates
- admin broadcasts

Notifications are stored and polled via API endpoints when needed.

## Payments and plans

The app includes billing logic for user plans and subscriptions. It can handle premium upgrades and billing callbacks.

## Admin controls

Admins can:

- review site activity
- change user plan status
- moderate content and settings
- manage settings and reports
- broadcast notifications

## Legal and compliance

The app includes policy pages and legal acceptance flows so the platform can manage:

- privacy policy
- terms of service
- acceptance tracking

## SEO and public site discovery

The app also includes sitemap generation and public URL services so created websites can be indexed and surfaced properly.
