# Admin Dashboard: Legal

## Purpose

The Legal module manages policy content, versioning, publishing, and user acceptance tracking.

## Primary Controller and Models

- `app/Controllers/LegalController.php`
- `app/Models/LegalPolicy.php`
- `app/Models/LegalPolicyVersion.php`
- `app/Models/PolicyAuditLog.php`
- `app/Models/UserPolicyAcceptance.php`

## Admin actions

- `adminIndex()` displays the legal policy center.
- `editPolicy()` loads a policy for editing or displays the creation form.
- `savePolicy()` persists policy metadata and version content.
- `publishVersion()` publishes a saved policy version.

## Policy flow

- Policies are stored as a two-table versioned model.
- Publishing a version marks it live and triggers `PolicyAuditLog::logPublish()`.
- Users are notified when policies are updated, and they must accept the latest published version.

## User acceptance

- `showAcceptancePage()` renders the acceptance page for authenticated users.
- `accept()` records acceptance using `UserPolicyAcceptance::recordAcceptance()`.
- An audit log is created for each acceptance event.

## Notes

- Notification delivery on policy publish is intentionally best-effort to preserve admin workflows.
- The acceptance endpoint enforces authentication and CSRF protection.
