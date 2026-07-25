-- Turns the dormant `notifications` table (created empty in the original dump,
-- never wired to any code) into the backing store for the in-app notification
-- feature: adds `type` (drives the icon/badge in the bell dropdown, see
-- NotificationService) and `link` (where clicking a notification takes the
-- user), and widens `message` into a short `title` + longer `message` body so
-- the dropdown can show a bold headline with detail underneath, matching how
-- every other list page in this app separates a title from its description.
ALTER TABLE notifications
    ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'general' AFTER user_id,
    ADD COLUMN title VARCHAR(150) NOT NULL DEFAULT '' AFTER type,
    ADD COLUMN link VARCHAR(255) NULL AFTER message,
    MODIFY COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0,
    ADD INDEX idx_user_unread (user_id, is_read),
    ADD INDEX idx_user_created (user_id, created_at);
