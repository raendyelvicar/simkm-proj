-- Converts the free-text diary into the structured CBT-style entry from
-- "Revisi - Diary Terstruktur": situasi -> pikiran otomatis -> emosi (+intensitas)
-- -> reaksi fisik -> perilaku, plus optional self reflection / gratitude journal /
-- rencana besok, and an explicit private-vs-shared-with-konselor choice.
-- judul/content/mood_level are relaxed to nullable rather than dropped, since the
-- structured fields now carry the entry and old rows may still hold values there.
ALTER TABLE diary_entries
    MODIFY judul VARCHAR(255) NULL,
    MODIFY content TEXT NULL,
    MODIFY mood_level VARCHAR(50) NULL,
    ADD COLUMN situasi TEXT NULL AFTER content,
    ADD COLUMN pikiran_awal TEXT NULL AFTER situasi,
    ADD COLUMN emosi_list JSON NULL AFTER pikiran_awal,
    ADD COLUMN emosi_lainnya VARCHAR(100) NULL AFTER emosi_list,
    ADD COLUMN intensitas_emosi TINYINT NULL AFTER emosi_lainnya,
    ADD COLUMN reaksi_fisik_list JSON NULL AFTER intensitas_emosi,
    ADD COLUMN reaksi_fisik_lainnya VARCHAR(100) NULL AFTER reaksi_fisik_list,
    ADD COLUMN perilaku TEXT NULL AFTER reaksi_fisik_lainnya,
    ADD COLUMN self_reflection TEXT NULL AFTER perilaku,
    ADD COLUMN gratitude_list JSON NULL AFTER self_reflection,
    ADD COLUMN rencana_besok TEXT NULL AFTER gratitude_list,
    ADD COLUMN shared_konselor_id INT NULL AFTER is_private,
    ADD CONSTRAINT fk_diary_shared_konselor
        FOREIGN KEY (shared_konselor_id) REFERENCES konselor(konselor_id) ON DELETE SET NULL;
