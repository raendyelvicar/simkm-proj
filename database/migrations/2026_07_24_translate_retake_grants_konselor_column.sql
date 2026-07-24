-- assessment_retake_grants was created (2026_07_21) after the schema/dump
-- snapshot the main rename migration was built from, so its konselor_id
-- column was missed there. Renames it to counselor_id to match the
-- `counselors` table and the application code, which already expects
-- counselor_id here.
ALTER TABLE assessment_retake_grants
    CHANGE COLUMN konselor_id counselor_id int NOT NULL;
