-- Marks a submission produced by the timed session flow finalizing before all
-- questions were answered, so staff views can show it wasn't fully completed.
ALTER TABLE assessment_submissions
    ADD COLUMN is_timed_out TINYINT(1) NOT NULL DEFAULT 0 AFTER category_percentage;
