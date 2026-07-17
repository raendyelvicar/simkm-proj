-- Seed data for BDI-II (21 items) and PWB / Ryff-18 (18 items).
-- Generated from the official Indonesian-validated instrument text supplied for this feature.

-- BDI-II questions (21) --
INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 1, 'Kesedihan', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak merasa sedih.', 0),
    (@qid, 1, 'Saya sering kali merasa sedih.', 1),
    (@qid, 2, 'Saya merasa sedih sepanjang waktu.', 2),
    (@qid, 3, 'Saya merasa sangat tidak bahagia atau sedih sampai tidak tertahankan.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 2, 'Pesimis', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak meragukan masa depan saya.', 0),
    (@qid, 1, 'Saya merasa lebih meragukan masa depan saya dibanding biasanya.', 1),
    (@qid, 2, 'Saya merasa segala sesuatu tidak berjalan dengan baik bagi saya.', 2),
    (@qid, 3, 'Saya merasa masa depan saya tidak ada harapan dan akan semakin buruk.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 3, 'Kegagalan masa lalu', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak merasa gagal.', 0),
    (@qid, 1, 'Saya telah gagal lebih dari yang seharusnya.', 1),
    (@qid, 2, 'Saya melakukan banyak kegagalan di masa lalu.', 2),
    (@qid, 3, 'Saya merasa gagal sama sekali (betul-betul gagal).', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 4, 'Kehilangan gairah', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya mendapatkan kesenangan dari hal-hal yang saya lakukan.', 0),
    (@qid, 1, 'Saya tidak menikmati sesuatu seperti biasanya.', 1),
    (@qid, 2, 'Saya hanya mendapatkan sangat sedikit kesenangan dari hal-hal yang biasanya bisa saya nikmati.', 2),
    (@qid, 3, 'Saya tidak mendapatkan kesenangan sama sekali dari hal-hal yang biasanya bisa saya nikmati.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 5, 'Perasaan bersalah', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya sama sekali tidak merasa bersalah.', 0),
    (@qid, 1, 'Saya merasa bersalah atas banyak hal yang telah atau seharusnya saya lakukan.', 1),
    (@qid, 2, 'Saya sering merasa bersalah.', 2),
    (@qid, 3, 'Saya merasa bersalah setiap saat.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 6, 'Perasaan dihukum', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak merasa bahwa saya sedang dihukum.', 0),
    (@qid, 1, 'Saya merasa bahwa mungkin saya akan dihukum.', 1),
    (@qid, 2, 'Saya yakin bahwa saya akan dihukum.', 2),
    (@qid, 3, 'Saya merasa bahwa saya sedang dihukum.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 7, 'Tidak menyukai diri sendiri', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak merasa kecewa pada diri sendiri.', 0),
    (@qid, 1, 'Saya kehilangan kepercayaan pada diri sendiri.', 1),
    (@qid, 2, 'Saya merasa kecewa pada diri sendiri.', 2),
    (@qid, 3, 'Saya benci pada diri sendiri.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 8, 'Mengkritik diri sendiri', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak mengkritik atau menyalahkan diri sendiri lebih dari biasanya.', 0),
    (@qid, 1, 'Saya mengkritik diri sendiri lebih dari biasanya.', 1),
    (@qid, 2, 'Saya mengkritik diri sendiri atas semua kesalahan yang saya lakukan.', 2),
    (@qid, 3, 'Saya menyalahkan diri sendiri untuk semua hal-hal buruk yang terjadi.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 9, 'Pikiran-pikiran atau keinginan bunuh diri', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak berpikir untuk bunuh diri.', 0),
    (@qid, 1, 'Saya berpikir untuk bunuh diri, tetapi hal itu tidak akan saya lakukan.', 1),
    (@qid, 2, 'Saya ingin bunuh diri.', 2),
    (@qid, 3, 'Saya akan bunuh diri seandainya ada kesempatan.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 10, 'Menangis', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak menangis lagi seperti biasanya.', 0),
    (@qid, 1, 'Saya lebih sering menangis dibanding biasanya.', 1),
    (@qid, 2, 'Saya menangis bahkan untuk masalah masalah kecil.', 2),
    (@qid, 3, 'Rasanya saya ingin sekali menangis tetapi tidak bisa.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 11, 'Gelisah', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak lagi merasa gelisah atau tertekan dibandingkan biasanya.', 0),
    (@qid, 1, 'Saya merasa lebih mudah gelisah atau tertekan dibanding biasanya.', 1),
    (@qid, 2, 'Saya sangat tertekan dan gelisah sampai sulit untuk berdiam diri.', 2),
    (@qid, 3, 'Saya sangat gelisah sehingga harus senantiasa bergerak atau melakukan sesuatu.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 12, 'Kehilangan minat', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak kehilangan minat untuk berelasi dengan orang lain atau melakukan aktivitas.', 0),
    (@qid, 1, 'Saya kurang berminat untuk berelasi dengan orang lain atau terhadap sesuatu dibandingkan biasanya.', 1),
    (@qid, 2, 'Saya kehilangan hampir seluruh minat saya untuk berelasi dengan orang lain atau terhadap sesuatu.', 2),
    (@qid, 3, 'Saya tidak berminat akan apapun.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 13, 'Sulit mengambil keputusan', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya dapat mengambil keputusan sebagaimana yang biasanya saya lakukan.', 0),
    (@qid, 1, 'Saya agak sulit mengambil keputusan dibanding biasanya.', 1),
    (@qid, 2, 'Saya lebih banyak mengalami kesulitan dalam mengambil keputusan dibanding biasanya.', 2),
    (@qid, 3, 'Saya sangat mengalami kesulitan setiap kali mengambil keputusan.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 14, 'Merasa tidak layak', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya merasa layak.', 0),
    (@qid, 1, 'Saya merasa tidak layak dan tidak berguna dibandingkan biasanya.', 1),
    (@qid, 2, 'Saya merasa lebih tidak layak dibanding orang lain.', 2),
    (@qid, 3, 'Saya merasa sama sekali tidak layak.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 15, 'Kehilangan tenaga (semangat)', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya memiliki tenaga (semangat) seperti biasanya.', 0),
    (@qid, 1, 'Saya memiliki tenaga lebih sedikit dibanding yang seharusnya saya miliki.', 1),
    (@qid, 2, 'Saya tidak memiliki tenaga yang cukup untuk berbuat banyak.', 2),
    (@qid, 3, 'Saya tidak memiliki tenaga yang cukup untuk melakukan apapun.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 16, 'Perubahan pola tidur', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak mengalami perubahan apapun dalam pola tidur saya.', 0),
    (@qid, 1, 'Saya tidur lebih dari biasanya.', 1),
    (@qid, 2, 'Saya tidur kurang dari biasanya.', 1),
    (@qid, 3, 'Saya tidur jauh lebih lama dari biasanya.', 2),
    (@qid, 4, 'Saya tidur sangat kurang dari biasanya.', 2),
    (@qid, 5, 'Saya tidur hampir sepanjang hari.', 3),
    (@qid, 6, 'Saya bangun 1-2 jam lebih awal dan tidak dapat tidur kembali.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 17, 'Mudah marah', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak lebih mudah marah seperti biasanya.', 0),
    (@qid, 1, 'Saya lebih mudah marah dibanding biasanya.', 1),
    (@qid, 2, 'Saya jauh lebih mudah marah dibanding biasanya.', 2),
    (@qid, 3, 'Saya mudah marah sepanjang waktu.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 18, 'Perubahan selera makan', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Selera makan saya tidak berubah (tidak lebih buruk) dari biasanya.', 0),
    (@qid, 1, 'Selera makan saya kurang dari biasanya.', 1),
    (@qid, 2, 'Selera makan saya lebih dari biasanya.', 1),
    (@qid, 3, 'Selera makan saya sangat kurang dibanding biasanya.', 2),
    (@qid, 4, 'Selera makan saya sangat lebih dibanding biasanya.', 2),
    (@qid, 5, 'Saya tidak punya selera makan sama sekali.', 3),
    (@qid, 6, 'Saya ingin makan setiap waktu.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 19, 'Sulit berkonsentrasi', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya mampu berkonsentrasi seperti biasanya.', 0),
    (@qid, 1, 'Saya tidak mampu berkonsentrasi seperti biasanya.', 1),
    (@qid, 2, 'Saya sangat sulit untuk tetap memusatkan pikiran terhadap sesuatu dalam jangka waktu yang panjang.', 2),
    (@qid, 3, 'Saya merasa saya tidak mampu berkonsentrasi dalam semua hal.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 20, 'Capek atau Kelelahan', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak lebih capek atau lelah dibanding biasanya.', 0),
    (@qid, 1, 'Saya lebih mudah capek atau lelah dari biasanya.', 1),
    (@qid, 2, 'Saya merasa capek atau lelah untuk melakukan banyak hal yang biasanya saya lakukan.', 2),
    (@qid, 3, 'Saya terlalu capek atau lelah untuk melakukan hampir semua hal yang biasanya saya lakukan.', 3);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('bdi2', 21, 'Kehilangan gairah seksual', NULL, 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 0, 'Saya tidak melihat adanya perubahan pada gairah seksual saya.', 0),
    (@qid, 1, 'Gairah seksual saya berkurang, tidak seperti biasanya.', 1),
    (@qid, 2, 'Saya menjadi sangat kurang berminat pada aktivitas seksual saat ini.', 2),
    (@qid, 3, 'Gairah seksual saya hilang sama sekali.', 3);

-- PWB / Ryff-18 questions (18) --
INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 1, 'Secara umum, saya merasa bertanggung jawab atas situasi lingkungan tempat saya tinggal', 'environmental_mastery', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 2, 'Ketika mengingat perjalanan hidup saya sendiri, senang rasanya melihat beberapa hal telah berubah.', 'self_acceptance', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 3, 'Menjaga hubungan dekat dengan orang lain adalah hal yang sulit bagi saya.', 'positive_relations', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 4, 'Tuntutan kehidupan sehari-hari sering membuat saya tertekan.', 'environmental_mastery', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 5, 'Saya menjalani hidup hanya untuk hari ini dan tidak terlalu memikirkan masa depan.', 'purpose_in_life', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 6, 'Saya cukup pandai mengelola banyak tanggung jawab kehidupan sehari-hari', 'environmental_mastery', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 7, 'Penting untuk memiliki pengalaman baru yang membuat saya dapat berpikir tentang diri sendiri dan dunia', 'personal_growth', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 8, 'Saya suka sebagian besar hal-hal yang ada dalam kepribadian saya', 'self_acceptance', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 9, 'Saya cenderung terpengaruh oleh orang-orang dengan pendapat yang kuat', 'autonomy', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 10, 'Dalam banyak hal, saya merasa kecewa dengan pencapaian hidup', 'self_acceptance', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 11, 'Orang-orang akan menggambarkan saya sebagai orang yang suka memberi dan senang berbagi waktu dengan orang lain', 'positive_relations', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 12, 'Saya yakin pada pendapat sendiri, bahkan jika hal itu bertentangan dengan pendapat umum', 'autonomy', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 13, 'Saya tidak memiliki banyak hubungan yang hangat dan saling percaya dengan orang lain', 'positive_relations', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 14, 'Beberapa orang tidak memiliki tujuan hidup, tapi saya bukan salah satu dari mereka', 'purpose_in_life', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 15, 'Bagi saya, hidup adalah proses belajar, berubah, dan berkembang terus menerus', 'personal_growth', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 16, 'Saya terkadang merasa seolah-olah semua usaha dalam hidup ini sudah dilakukan', 'purpose_in_life', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 17, 'Saya menyerah dalam usaha untuk melakukan perbaikan besar atau perubahan dalam hidup', 'personal_growth', 1);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

INSERT INTO assessment_questions (type, order_no, question_text, dimension, is_reverse_scored) VALUES ('pwb', 18, 'Saya menilai diri sendiri berdasarkan apa yang saya anggap penting secara pribadi, bukan oleh nilai-nilai yang dianggap penting oleh orang lain', 'autonomy', 0);
SET @qid = LAST_INSERT_ID();
INSERT INTO assessment_choices (question_id, order_no, label, score_value) VALUES
    (@qid, 1, 'Sangat Tidak Setuju (STS)', 1),
    (@qid, 2, 'Tidak Setuju (TS)', 2),
    (@qid, 3, 'Agak Tidak Setuju (ATS)', 3),
    (@qid, 4, 'Agak Setuju (AS)', 4),
    (@qid, 5, 'Setuju (S)', 5),
    (@qid, 6, 'Sangat Setuju (SS)', 6);

