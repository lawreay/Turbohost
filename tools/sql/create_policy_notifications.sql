-- Create in-app notifications for all users about updated published policies.
-- Non-destructive: inserts one notification per user. Adjust message/title as desired.

INSERT INTO notifications (user_id, category, icon, title, message, target_url, is_read, email_sent)
SELECT id,
       'legal' AS category,
       'file-text' AS icon,
       'Platform policies updated' AS title,
       'Please review and accept the updated platform policies on your dashboard.' AS message,
       '/legal/accept' AS target_url,
       0 AS is_read,
       0 AS email_sent
FROM users;
