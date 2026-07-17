-- Combined, timed BDI-II + PWB session flow: one continuous attempt per user
-- instead of two separate untimed single-page submissions.

-- Admin-editable key/value settings (currently only the combined time limit).
CREATE TABLE app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO app_settings (setting_key, setting_value) VALUES ('assessment_time_limit_minutes', '45');

-- One row per combined BDI-II+PWB attempt.
CREATE TABLE assessment_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- matches users.id, which is signed INT (not UNSIGNED)
    status ENUM('in_progress', 'completed', 'timed_out') NOT NULL DEFAULT 'in_progress',
    time_limit_seconds INT UNSIGNED NOT NULL, -- snapshot at start; admin changes never retroactively affect this
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL, -- authoritative deadline, computed at INSERT from started_at + limit
    last_seen_question_id INT UNSIGNED NULL, -- UX resume pointer only, not authoritative for scoring
    bdi2_submission_id INT UNSIGNED NULL,
    pwb_submission_id INT UNSIGNED NULL,
    finalized_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (bdi2_submission_id) REFERENCES assessment_submissions(id) ON DELETE SET NULL,
    FOREIGN KEY (pwb_submission_id) REFERENCES assessment_submissions(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Draft answers for an in-progress session, upserted as the user answers/re-answers
-- (jump-navigation lets them revisit and change a question before finishing).
CREATE TABLE assessment_session_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    choice_id INT UNSIGNED NOT NULL,
    score_value INT NOT NULL,
    answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_session_question (session_id, question_id),
    FOREIGN KEY (session_id) REFERENCES assessment_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES assessment_questions(id),
    FOREIGN KEY (choice_id) REFERENCES assessment_choices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
