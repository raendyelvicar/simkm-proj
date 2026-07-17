-- Daily tips shown to mahasiswa as a popup right after login.
-- Managed (add/update/delete) by konselor accounts.
CREATE TABLE daily_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    daily_tips TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
