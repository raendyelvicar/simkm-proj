DROP TABLE IF EXISTS konselor;
CREATE TABLE konselor (
    konselor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nomor_registrasi VARCHAR(50) NOT NULL UNIQUE,
    profesi ENUM('Psikolog','Konselor','Psikiater') NOT NULL,
    spesialisasi VARCHAR(100),
    pendidikan VARCHAR(150),
    pengalaman_tahun INT DEFAULT 0,
    bahasa VARCHAR(100),
    biaya_konsultasi DECIMAL(12,2) DEFAULT 0,
    durasi_sesi INT DEFAULT 60,
    metode_konsultasi ENUM('Online','Offline','Hybrid') DEFAULT 'Online',
    foto_profil VARCHAR(255),
    biografi TEXT,
    status_verifikasi BOOLEAN DEFAULT FALSE,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_konselor_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE konselor_jadwal (
    jadwal_id INT AUTO_INCREMENT PRIMARY KEY,
    konselor_id INT NOT NULL,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    kuota INT DEFAULT 10,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_jadwal_konselor FOREIGN KEY(konselor_id) REFERENCES konselor(konselor_id) ON DELETE CASCADE
);

CREATE TABLE booking_konseling (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    konselor_id INT NOT NULL,
    jadwal_id INT,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    keluhan TEXT,
    status ENUM('Pending','Confirmed','Completed','Cancelled','No Show') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(konselor_id) REFERENCES konselor(konselor_id),
    FOREIGN KEY(jadwal_id) REFERENCES konselor_jadwal(jadwal_id)
);

CREATE TABLE sesi_konseling (
    sesi_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    catatan_konselor TEXT,
    rekomendasi TEXT,
    tindak_lanjut TEXT,
    durasi INT,
    selesai_pada DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(booking_id) REFERENCES booking_konseling(booking_id) ON DELETE CASCADE
);

CREATE TABLE rating_konselor (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL,
    konselor_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(booking_id) REFERENCES booking_konseling(booking_id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(konselor_id) REFERENCES konselor(konselor_id)
);

CREATE TABLE konselor_sertifikasi (
    sertifikasi_id INT AUTO_INCREMENT PRIMARY KEY,
    konselor_id INT NOT NULL,
    nama_sertifikasi VARCHAR(200) NOT NULL,
    penerbit VARCHAR(150),
    nomor_sertifikat VARCHAR(100),
    tahun YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(konselor_id) REFERENCES konselor(konselor_id) ON DELETE CASCADE
);

