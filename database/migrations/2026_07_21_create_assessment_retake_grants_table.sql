-- Gate on retaking the combined self-assessment: a mahasiswa's very first session is
-- always allowed; every session after that requires an unconsumed grant here, created
-- only when a konselor explicitly recommends it while completing a booking (see
-- BookingQueueController::complete()). One grant = exactly one retake.
CREATE TABLE assessment_retake_grants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    counselor_id INT NOT NULL,
    granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    consumed_at DATETIME NULL,
    consumed_session_id INT UNSIGNED NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES counseling_bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (counselor_id) REFERENCES counselors(counselor_id) ON DELETE CASCADE,
    FOREIGN KEY (consumed_session_id) REFERENCES assessment_sessions(id) ON DELETE SET NULL,
    INDEX idx_user_unconsumed (user_id, consumed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
