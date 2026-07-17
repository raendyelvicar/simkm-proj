-- konselor_jadwal: weekly recurrence -> specific calendar date.
-- Nullable-then-backfill-then-NOT-NULL so existing rows (including ones
-- referenced by real booking_konseling rows via FK) don't get rejected or deleted.
ALTER TABLE konselor_jadwal ADD COLUMN tanggal DATE NULL AFTER konselor_id;
UPDATE konselor_jadwal SET tanggal = DATE(created_at) WHERE tanggal IS NULL;
ALTER TABLE konselor_jadwal MODIFY tanggal DATE NOT NULL;
ALTER TABLE konselor_jadwal DROP COLUMN hari;

CREATE TABLE monitoring_periods (
    monitoring_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    konselor_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(booking_id) REFERENCES booking_konseling(booking_id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(konselor_id) REFERENCES konselor(konselor_id)
);
