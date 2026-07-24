-- Renames the remaining Indonesian-named tables/columns to English, and
-- translates the small set of controlled enum values (role, gender,
-- profession, assessment review status) that the application logic
-- branches on. Free-text data (diary entries, chat messages, articles,
-- comments, complaints) is never touched by this migration.
--
-- Order matters: enum columns are widened to accept both the old and new
-- values, the data is updated, then the column is narrowed back down to the
-- final English-only enum -- this keeps every row valid at every step.

-- ---------------------------------------------------------------------
-- users: column renames + enum value translation
-- ---------------------------------------------------------------------
ALTER TABLE users
    CHANGE COLUMN nama name varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN nama_lengkap full_name varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN npm student_number varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN fakultas faculty varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN jurusan major varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN no_hp phone_number varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN jenis_kelamin gender enum('Laki-laki','Perempuan','Male','Female') COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN role role enum('admin','mahasiswa','konselor','student','counselor') COLLATE utf8mb4_general_ci DEFAULT NULL;

UPDATE users SET gender = 'Male' WHERE gender = 'Laki-laki';
UPDATE users SET gender = 'Female' WHERE gender = 'Perempuan';
UPDATE users SET role = 'student' WHERE role = 'mahasiswa';
UPDATE users SET role = 'counselor' WHERE role = 'konselor';

ALTER TABLE users
    MODIFY COLUMN gender enum('Male','Female') COLLATE utf8mb4_general_ci DEFAULT NULL,
    MODIFY COLUMN role enum('admin','student','counselor') COLLATE utf8mb4_general_ci DEFAULT NULL;

-- ---------------------------------------------------------------------
-- mahasiswa -> students
-- ---------------------------------------------------------------------
ALTER TABLE mahasiswa
    CHANGE COLUMN id_mahasiswa student_id int NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN npm student_number varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
    CHANGE COLUMN program_studi study_program varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    CHANGE COLUMN fakultas faculty varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    CHANGE COLUMN no_hp phone_number varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN jenis_kelamin gender enum('L','P','M','F') COLLATE utf8mb4_general_ci NOT NULL;

UPDATE mahasiswa SET gender = 'M' WHERE gender = 'L';
UPDATE mahasiswa SET gender = 'F' WHERE gender = 'P';

ALTER TABLE mahasiswa
    MODIFY COLUMN gender enum('M','F') COLLATE utf8mb4_general_ci NOT NULL;

RENAME TABLE mahasiswa TO students;

-- ---------------------------------------------------------------------
-- fakultas -> faculties
-- ---------------------------------------------------------------------
RENAME TABLE fakultas TO faculties;

-- ---------------------------------------------------------------------
-- jurusan -> majors
-- ---------------------------------------------------------------------
ALTER TABLE jurusan
    CHANGE COLUMN fakultas_id faculty_id int NOT NULL;
RENAME TABLE jurusan TO majors;

-- ---------------------------------------------------------------------
-- konselor -> counselors: column renames + profession enum translation
-- ---------------------------------------------------------------------
ALTER TABLE konselor
    CHANGE COLUMN konselor_id counselor_id int NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN nomor_registrasi registration_number varchar(50) NOT NULL,
    CHANGE COLUMN profesi profession enum('Psikolog','Konselor','Psikiater','Psychologist','Counselor','Psychiatrist') NOT NULL,
    CHANGE COLUMN spesialisasi specialization varchar(100) DEFAULT NULL,
    CHANGE COLUMN pendidikan education varchar(150) DEFAULT NULL,
    CHANGE COLUMN pengalaman_tahun experience_years int DEFAULT '0',
    CHANGE COLUMN bahasa languages varchar(100) DEFAULT NULL,
    CHANGE COLUMN biaya_konsultasi consultation_fee decimal(12,2) DEFAULT '0.00',
    CHANGE COLUMN durasi_sesi session_duration int DEFAULT '60',
    CHANGE COLUMN metode_konsultasi consultation_method enum('Online','Offline','Hybrid') DEFAULT 'Online',
    CHANGE COLUMN foto_profil profile_photo varchar(255) DEFAULT NULL,
    CHANGE COLUMN biografi biography text,
    CHANGE COLUMN status_verifikasi verification_status tinyint(1) DEFAULT '0',
    CHANGE COLUMN status_aktif is_active tinyint(1) DEFAULT '1';

UPDATE konselor SET profession = 'Psychologist' WHERE profession = 'Psikolog';
UPDATE konselor SET profession = 'Counselor' WHERE profession = 'Konselor';
UPDATE konselor SET profession = 'Psychiatrist' WHERE profession = 'Psikiater';

ALTER TABLE konselor
    MODIFY COLUMN profession enum('Psychologist','Counselor','Psychiatrist') NOT NULL;

RENAME TABLE konselor TO counselors;

-- ---------------------------------------------------------------------
-- konselor_jadwal -> counselor_schedules
-- ---------------------------------------------------------------------
ALTER TABLE konselor_jadwal
    CHANGE COLUMN jadwal_id schedule_id int NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN konselor_id counselor_id int NOT NULL,
    CHANGE COLUMN tanggal `date` date NOT NULL,
    CHANGE COLUMN jam_mulai start_time time NOT NULL,
    CHANGE COLUMN jam_selesai end_time time NOT NULL,
    CHANGE COLUMN kuota quota int DEFAULT '10',
    CHANGE COLUMN status_aktif is_active tinyint(1) DEFAULT '1';
RENAME TABLE konselor_jadwal TO counselor_schedules;

-- ---------------------------------------------------------------------
-- konselor_sertifikasi -> counselor_certifications
-- ---------------------------------------------------------------------
ALTER TABLE konselor_sertifikasi
    CHANGE COLUMN sertifikasi_id certification_id int NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN konselor_id counselor_id int NOT NULL,
    CHANGE COLUMN nama_sertifikasi certification_name varchar(200) NOT NULL,
    CHANGE COLUMN penerbit issuer varchar(150) DEFAULT NULL,
    CHANGE COLUMN nomor_sertifikat certificate_number varchar(100) DEFAULT NULL,
    CHANGE COLUMN tahun year year DEFAULT NULL;
RENAME TABLE konselor_sertifikasi TO counselor_certifications;

-- ---------------------------------------------------------------------
-- booking_konseling -> counseling_bookings
-- ---------------------------------------------------------------------
ALTER TABLE booking_konseling
    CHANGE COLUMN konselor_id counselor_id int NOT NULL,
    CHANGE COLUMN jadwal_id schedule_id int DEFAULT NULL,
    CHANGE COLUMN tanggal date date NOT NULL,
    CHANGE COLUMN jam_mulai start_time time NOT NULL,
    CHANGE COLUMN jam_selesai end_time time NOT NULL,
    CHANGE COLUMN keluhan complaint text;
RENAME TABLE booking_konseling TO counseling_bookings;

-- ---------------------------------------------------------------------
-- rating_konselor -> counselor_ratings
-- ---------------------------------------------------------------------
ALTER TABLE rating_konselor
    CHANGE COLUMN konselor_id counselor_id int NOT NULL,
    CHANGE COLUMN komentar comment text;
RENAME TABLE rating_konselor TO counselor_ratings;

-- ---------------------------------------------------------------------
-- sesi_konseling -> counseling_sessions
-- ---------------------------------------------------------------------
ALTER TABLE sesi_konseling
    CHANGE COLUMN sesi_id session_id int NOT NULL AUTO_INCREMENT,
    CHANGE COLUMN catatan_konselor counselor_notes text,
    CHANGE COLUMN rekomendasi recommendation text,
    CHANGE COLUMN tindak_lanjut follow_up text,
    CHANGE COLUMN durasi duration int DEFAULT NULL,
    CHANGE COLUMN selesai_pada completed_at datetime DEFAULT NULL;
RENAME TABLE sesi_konseling TO counseling_sessions;

-- ---------------------------------------------------------------------
-- log_login -> login_logs
-- ---------------------------------------------------------------------
ALTER TABLE log_login
    CHANGE COLUMN waktu_login login_time datetime DEFAULT CURRENT_TIMESTAMP;
RENAME TABLE log_login TO login_logs;

-- ---------------------------------------------------------------------
-- diary_entries: column renames (table name unchanged)
-- ---------------------------------------------------------------------
ALTER TABLE diary_entries
    CHANGE COLUMN judul title varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN situasi situation text COLLATE utf8mb4_general_ci,
    CHANGE COLUMN pikiran_awal initial_thoughts text COLLATE utf8mb4_general_ci,
    CHANGE COLUMN emosi_list emotions_list json DEFAULT NULL,
    CHANGE COLUMN emosi_lainnya other_emotions varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN intensitas_emosi emotion_intensity tinyint DEFAULT NULL,
    CHANGE COLUMN reaksi_fisik_list physical_reactions_list json DEFAULT NULL,
    CHANGE COLUMN reaksi_fisik_lainnya other_physical_reactions varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN perilaku behavior text COLLATE utf8mb4_general_ci,
    CHANGE COLUMN rencana_besok tomorrow_plan text COLLATE utf8mb4_general_ci,
    CHANGE COLUMN shared_konselor_id shared_counselor_id int DEFAULT NULL;

-- ---------------------------------------------------------------------
-- diary_responses / monitoring_periods: bare konselor_id column rename
-- ---------------------------------------------------------------------
ALTER TABLE diary_responses
    CHANGE COLUMN konselor_id counselor_id int DEFAULT NULL;
ALTER TABLE monitoring_periods
    CHANGE COLUMN konselor_id counselor_id int NOT NULL;

-- ---------------------------------------------------------------------
-- assessment_results: column renames + review status enum translation
-- ---------------------------------------------------------------------
ALTER TABLE assessment_results
    CHANGE COLUMN tanggal_tes test_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHANGE COLUMN total_skor total_score int NOT NULL,
    CHANGE COLUMN kesimpulan conclusion varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    CHANGE COLUMN saran_tindakan recommended_action text COLLATE utf8mb4_general_ci,
    CHANGE COLUMN status_review review_status enum('Belum Dilihat','Sudah Direview','Not Reviewed','Reviewed') COLLATE utf8mb4_general_ci DEFAULT 'Belum Dilihat';

UPDATE assessment_results SET review_status = 'Not Reviewed' WHERE review_status = 'Belum Dilihat';
UPDATE assessment_results SET review_status = 'Reviewed' WHERE review_status = 'Sudah Direview';

ALTER TABLE assessment_results
    MODIFY COLUMN review_status enum('Not Reviewed','Reviewed') COLLATE utf8mb4_general_ci DEFAULT 'Not Reviewed';

-- ---------------------------------------------------------------------
-- questions: column renames (table name unchanged)
-- ---------------------------------------------------------------------
ALTER TABLE questions
    CHANGE COLUMN kategori category varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    CHANGE COLUMN bobot_skor score_weight int NOT NULL,
    CHANGE COLUMN tipe_pilihan choice_type varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Likert Scale';
