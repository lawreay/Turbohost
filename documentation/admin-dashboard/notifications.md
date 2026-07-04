# Admin Dashboard: Notifications

## Purpose

The Notifications module enables administrators to send system-wide announcements, review delivered notifications, and confirm notification states.

## Primary Controller and Services

- `app/Controllers/AdminController.php`
- `app/Services\NotificationManager.php`
- `app/Services\NotificationService.php`
- `app/Models\Notification.php`

## Key actions

- `notifications()` loads the notification list for admin review.
- `broadcastNotifications()` sends either in-app-only messages or email-backed broadcasts to all users.

## Workflow

- Admin broadcasts can include a title and message.
- If `broadcast_send_email` is set, the system iterates all user IDs and sends an email notification per user.
- Otherwise, the system creates a platform announcement using `NotificationManager::broadcastAnnouncement()`.

## Notes

- Broadcast email errors are ignored per-user to avoid blocking delivery to other recipients.
- Notifications are used for user-facing events like policy updates, plan changes, and system announcements.
