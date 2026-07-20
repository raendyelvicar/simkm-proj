-- Positive Activity Planner (behavioral activation): mahasiswa plans a small
-- positive activity, then logs mood before/after completing it. Part of the
-- "aktivitas positif" self-help feature referenced in AssessmentScoringService's
-- combined-level recommendations (level 2+).
CREATE TABLE self_help_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    planned_date DATE NOT NULL,
    mood_before TINYINT NULL,
    mood_after TINYINT NULL,
    status ENUM('planned', 'done', 'skipped') NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
