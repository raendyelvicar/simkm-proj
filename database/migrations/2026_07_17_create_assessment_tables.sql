-- Self-assessment feature: BDI-II (depression) and PWB/Ryff-18 (psychological well-being).
-- Questions/choices are data-driven so both instruments render/score from the same tables.

CREATE TABLE assessment_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('bdi2', 'pwb') NOT NULL,
    order_no INT UNSIGNED NOT NULL,
    question_text VARCHAR(500) NOT NULL,
    dimension VARCHAR(50) NULL,
    is_reverse_scored TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_order (type, order_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assessment_choices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    order_no INT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    score_value INT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES assessment_questions(id) ON DELETE CASCADE,
    INDEX idx_question_order (question_id, order_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assessment_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- matches users.id, which is signed INT (not UNSIGNED)
    type ENUM('bdi2', 'pwb') NOT NULL,
    total_score INT NOT NULL,
    max_score INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    category_percentage DECIMAL(5,2) NULL,
    dimension_scores JSON NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_type_category (type, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assessment_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    choice_id INT UNSIGNED NOT NULL,
    score_value INT NOT NULL,
    FOREIGN KEY (submission_id) REFERENCES assessment_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES assessment_questions(id),
    FOREIGN KEY (choice_id) REFERENCES assessment_choices(id),
    INDEX idx_submission (submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
