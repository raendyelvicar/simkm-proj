-- Gates a student-initiated booking cancellation behind Admin approval instead of
-- cancelling immediately: BookingController::cancel() now parks the booking in
-- 'Cancellation Requested' and inserts a row here rather than setting status straight
-- to 'Cancelled'. AdminBookingCancellationController then either approves it (-> the
-- booking becomes 'Cancelled') or rejects it (-> the booking reverts to whatever status
-- it had before the request, so a Confirmed booking's monitoring/chat access survives
-- a rejected cancellation).
ALTER TABLE counseling_bookings
    MODIFY COLUMN status enum('Pending','Confirmed','Completed','Cancelled','No Show','Cancellation Requested') DEFAULT 'Pending';

CREATE TABLE booking_cancellation_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    previous_status enum('Pending','Confirmed') NOT NULL,
    reason TEXT NULL,
    status enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    admin_notes TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES counseling_bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
