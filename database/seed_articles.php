<?php

/**
 * Mengosongkan tabel `articles` lalu mengisinya dengan 15 artikel psikologi
 * dummy berbahasa Indonesia, masing-masing dengan gambar sampul (dibuat oleh
 * generate_article_images.php -> public/uploads/articles/psych_XX.jpg).
 *
 * Jalankan generate_article_images.py terlebih dahulu agar file gambarnya ada.
 *
 * Penggunaan: php database/seed_articles.php
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

function seed_env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli(
    seed_env('DB_HOST', '127.0.0.1'),
    seed_env('DB_USERNAME', 'root'),
    seed_env('DB_PASSWORD', ''),
    seed_env('DB_DATABASE', 'mental_health'),
    (int) seed_env('DB_PORT', '3306')
);
$mysqli->set_charset('utf8mb4');

// Penulis semua artikel dummy ini: akun admin utama (lihat seed di mental_health_dump.sql).
$authorId = (int) ($mysqli->query("SELECT id FROM users WHERE username = 'admin01' LIMIT 1")->fetch_assoc()['id'] ?? 39);

$articles = [
    [
        'slug' => 'psych_01',
        'category' => 'Kecemasan',
        'title' => 'Mengenal Gejala Kecemasan pada Mahasiswa',
        'tags' => 'kecemasan, anxiety, mahasiswa, kesehatan mental',
        'content' => <<<TXT
Kecemasan adalah respons alami tubuh terhadap tekanan atau ancaman, dan dalam kadar wajar sebenarnya membantu kita tetap waspada — misalnya sebelum ujian atau presentasi. Namun bagi banyak mahasiswa, kecemasan bisa berkembang menjadi sesuatu yang jauh lebih mengganggu: jantung berdebar tanpa sebab jelas, sulit berkonsentrasi, pikiran yang terus berputar memikirkan skenario terburuk, hingga gangguan tidur dan pola makan.

Beberapa gejala yang perlu diwaspadai antara lain rasa khawatir berlebihan yang sulit dikendalikan, ketegangan otot, mudah lelah, gelisah, serta menghindari situasi tertentu (seperti presentasi di kelas atau bertemu banyak orang) karena takut akan penilaian orang lain. Jika gejala ini berlangsung hampir setiap hari selama beberapa minggu dan mulai mengganggu aktivitas kuliah, ini adalah sinyal untuk tidak mengabaikannya.

Langkah pertama yang bisa dicoba adalah mengenali pemicunya — apakah terkait tugas, hubungan sosial, atau ekspektasi terhadap diri sendiri. Menuliskan perasaan ini secara rutin lewat Diary dapat membantu melihat pola yang mungkin tidak disadari. Latihan pernapasan sederhana juga terbukti membantu menurunkan intensitas kecemasan dalam jangka pendek.

Jika kecemasan terasa terus-menerus dan sulit diatasi sendiri, mengisi Self-Assessment di aplikasi ini bisa menjadi langkah awal untuk memahami tingkat keparahannya, dan berkonsultasi dengan konselor kampus adalah langkah yang sangat dianjurkan — bukan tanda kelemahan, melainkan bentuk kepedulian terhadap diri sendiri.
TXT,
    ],
    [
        'slug' => 'psych_02',
        'category' => 'Stres Akademik',
        'title' => 'Cara Mengatasi Stres Akademik Secara Efektif',
        'tags' => 'stres, akademik, kuliah, produktivitas',
        'content' => <<<TXT
Tuntutan tugas yang menumpuk, jadwal ujian yang berdekatan, serta ekspektasi nilai yang tinggi membuat stres akademik menjadi hal yang hampir dialami setiap mahasiswa. Dalam jumlah kecil, stres bisa menjadi dorongan untuk lebih fokus. Namun ketika berlangsung terus-menerus tanpa jeda, stres akademik dapat menurunkan kualitas belajar, memicu kelelahan mental, bahkan berdampak pada kesehatan fisik.

Salah satu penyebab utama stres akademik adalah manajemen waktu yang kurang baik, sehingga tugas menumpuk mendekati tenggat. Selain itu, perbandingan diri dengan pencapaian teman sebaya dan rasa takut mengecewakan orang tua juga sering menjadi beban tambahan yang tidak terlihat dari luar.

Beberapa cara yang terbukti membantu antara lain memecah tugas besar menjadi langkah-langkah kecil yang lebih mudah dikerjakan, menetapkan prioritas harian yang realistis, dan memberi diri sendiri waktu istirahat yang terjadwal — bukan hanya ketika sudah kelelahan. Berbicara dengan teman atau keluarga tentang beban yang dirasakan juga dapat meringankan tekanan secara emosional.

Jika stres akademik mulai memengaruhi mood dan performa secara signifikan, ada baiknya untuk mencatat perkembangannya lewat Diary Terstruktur di aplikasi ini, sehingga pola stres dapat dikenali lebih awal sebelum berkembang menjadi burnout.
TXT,
    ],
    [
        'slug' => 'psych_03',
        'category' => 'Self-Assessment',
        'title' => 'Pentingnya Self-Assessment bagi Kesehatan Mental',
        'tags' => 'self-assessment, bdi-ii, pwb, evaluasi diri',
        'content' => <<<TXT
Banyak orang baru menyadari adanya masalah kesehatan mental ketika kondisinya sudah cukup berat. Padahal, seperti halnya kesehatan fisik yang perlu dicek secara berkala, kesehatan mental juga membutuhkan evaluasi rutin agar perubahan kecil dapat dikenali sejak dini. Di sinilah pentingnya self-assessment — proses mengevaluasi kondisi psikologis diri sendiri secara terstruktur menggunakan instrumen yang telah teruji secara ilmiah.

Di aplikasi ini, self-assessment menggunakan dua instrumen utama: BDI-II (Beck Depression Inventory) untuk mengukur indikasi gejala depresi, dan PWB (Psychological Well-Being) untuk menilai aspek kesejahteraan psikologis seperti penerimaan diri, hubungan positif dengan orang lain, dan tujuan hidup. Kombinasi keduanya memberikan gambaran yang lebih menyeluruh dibanding hanya melihat satu sisi saja.

Penting untuk dipahami bahwa hasil self-assessment bukanlah diagnosis medis. Hasil ini lebih berfungsi sebagai gambaran awal yang membantu kamu — dan jika diperlukan, konselor kampus — memahami kondisi yang sedang dialami, serta menjadi dasar untuk menentukan langkah lanjutan yang sesuai, mulai dari self-help mandiri hingga konsultasi profesional.

Melakukan self-assessment secara berkala, bukan hanya sekali, memungkinkan kamu melihat tren perubahan kondisi mental dari waktu ke waktu. Jika hasil menunjukkan kategori yang perlu perhatian lebih, jangan ragu untuk memanfaatkan fitur konsultasi dengan konselor yang tersedia di aplikasi ini.
TXT,
    ],
    [
        'slug' => 'psych_04',
        'category' => 'Burnout',
        'title' => 'Burnout Akademik: Kenali Sebelum Terlambat',
        'tags' => 'burnout, kelelahan, motivasi, mahasiswa',
        'content' => <<<TXT
Burnout akademik adalah kondisi kelelahan emosional, mental, dan fisik yang muncul akibat tekanan akademik yang berkepanjangan tanpa pemulihan yang cukup. Berbeda dengan rasa lelah biasa yang hilang setelah istirahat semalam, burnout membuat seseorang merasa hampa, kehilangan motivasi belajar, dan sinis terhadap tugas-tugas yang dulunya terasa bermakna.

Tanda-tanda burnout yang sering muncul antara lain merasa lelah meski sudah cukup tidur, menunda-nunda pekerjaan secara berlebihan, sulit merasakan pencapaian meski telah menyelesaikan sesuatu, serta menarik diri dari kegiatan sosial maupun akademik. Jika dibiarkan, burnout dapat berkembang menjadi masalah kesehatan mental yang lebih serius seperti depresi.

Pemulihan dari burnout membutuhkan lebih dari sekadar libur singkat. Diperlukan evaluasi ulang terhadap beban yang diambil, penetapan batasan yang lebih sehat (misalnya berani berkata tidak pada komitmen tambahan), serta memberi ruang untuk aktivitas yang benar-benar dinikmati di luar konteks akademik.

Fitur Self Help di aplikasi ini — seperti latihan pernapasan dan rencana aktivitas positif — dirancang khusus untuk membantu memulihkan energi secara bertahap. Jika burnout sudah terasa berat dan berkepanjangan, berkonsultasi dengan konselor kampus akan membantu menyusun strategi pemulihan yang lebih terarah.
TXT,
    ],
    [
        'slug' => 'psych_05',
        'category' => 'Relaksasi',
        'title' => 'Teknik Relaksasi Sederhana untuk Menenangkan Pikiran',
        'tags' => 'relaksasi, pernapasan, ketenangan, stres',
        'content' => <<<TXT
Ketika pikiran terasa penuh dan sulit fokus, tubuh sebenarnya sedang memberi sinyal untuk melambat sejenak. Teknik relaksasi adalah cara sederhana namun efektif untuk menenangkan sistem saraf yang sedang tegang, dan yang menariknya, teknik ini bisa dilakukan kapan saja tanpa memerlukan alat khusus.

Salah satu teknik yang paling mudah dipelajari adalah box breathing atau pernapasan kotak: tarik napas selama empat detik, tahan empat detik, embuskan empat detik, lalu tahan kembali empat detik sebelum mengulang siklusnya. Teknik ini membantu menurunkan detak jantung dan memberi sinyal pada otak bahwa tubuh sedang dalam kondisi aman.

Selain pernapasan, teknik grounding 5-4-3-2-1 juga banyak digunakan untuk mengalihkan pikiran dari kecemasan berlebihan dengan menyadari sekeliling melalui panca indra — menyebutkan lima hal yang bisa dilihat, empat yang bisa disentuh, tiga yang bisa didengar, dan seterusnya. Teknik ini sangat berguna saat perasaan cemas muncul tiba-tiba di tempat umum.

Meluangkan waktu 5-10 menit setiap hari untuk berlatih teknik relaksasi, bahkan saat sedang tidak merasa cemas, akan membuat tubuh lebih terbiasa dan lebih cepat kembali tenang saat menghadapi situasi yang menekan. Fitur Latihan Pernapasan pada menu Self Help di aplikasi ini bisa menjadi titik awal yang baik untuk membiasakan diri.
TXT,
    ],
    [
        'slug' => 'psych_06',
        'category' => 'Depresi',
        'title' => 'Memahami Depresi: Gejala, Penyebab, dan Cara Mengatasinya',
        'tags' => 'depresi, gejala, kesehatan mental, dukungan',
        'content' => <<<TXT
Depresi jauh berbeda dengan rasa sedih biasa yang datang dan pergi. Depresi adalah kondisi kesehatan mental yang memengaruhi cara seseorang berpikir, merasa, dan menjalani aktivitas sehari-hari, umumnya berlangsung dalam waktu yang cukup lama dan tidak selalu memiliki penyebab yang jelas atau tunggal.

Gejala umum depresi meliputi perasaan sedih atau hampa yang menetap, kehilangan minat terhadap aktivitas yang biasanya disukai, perubahan pola tidur dan nafsu makan, sulit berkonsentrasi, merasa tidak berharga atau bersalah secara berlebihan, hingga dalam kondisi yang lebih berat, muncul pikiran untuk menyakiti diri sendiri. Jika gejala-gejala ini berlangsung lebih dari dua minggu dan mengganggu fungsi sehari-hari, penting untuk mencari bantuan.

Penyebab depresi bersifat kompleks — bisa berasal dari faktor biologis, pengalaman hidup yang berat, pola pikir yang terbentuk sejak lama, maupun kombinasi dari semuanya. Karena itu, depresi bukanlah sesuatu yang bisa diatasi hanya dengan "berpikir positif", dan tidak ada yang salah dengan mencari bantuan profesional untuk mengatasinya.

Jika kamu atau seseorang yang kamu kenal menunjukkan tanda-tanda ini, langkah pertama yang bisa diambil adalah berbicara dengan orang yang dipercaya dan mempertimbangkan konsultasi dengan konselor kampus. Fitur self-assessment BDI-II di aplikasi ini dapat membantu memberikan gambaran awal, namun penanganan yang tepat tetap memerlukan pendampingan profesional.
TXT,
    ],
    [
        'slug' => 'psych_07',
        'category' => 'Manajemen Waktu',
        'title' => 'Manajemen Waktu untuk Mengurangi Tekanan Kuliah',
        'tags' => 'manajemen waktu, produktivitas, kuliah, prioritas',
        'content' => <<<TXT
Salah satu sumber tekanan terbesar bagi mahasiswa bukanlah banyaknya tugas itu sendiri, melainkan bagaimana waktu dikelola untuk menyelesaikannya. Tanpa perencanaan yang baik, tugas-tugas kecil bisa menumpuk menjadi beban besar yang datang bersamaan, memicu stres yang sebenarnya bisa dihindari.

Salah satu pendekatan yang banyak digunakan adalah metode Eisenhower Matrix, yaitu membagi tugas berdasarkan urgensi dan kepentingannya. Tugas yang mendesak dan penting dikerjakan lebih dulu, sementara tugas yang penting namun tidak mendesak dijadwalkan agar tidak menjadi krisis di kemudian hari. Cara ini membantu menghindari kebiasaan mengerjakan semuanya di detik-detik terakhir.

Teknik lain yang cukup populer adalah Pomodoro, yaitu bekerja fokus selama 25 menit lalu beristirahat singkat selama 5 menit. Metode ini membantu menjaga konsentrasi tanpa membuat otak kelelahan karena bekerja terus-menerus tanpa jeda.

Yang tidak kalah penting, manajemen waktu yang sehat juga berarti menyisakan waktu untuk istirahat, bersosialisasi, dan melakukan hal-hal yang disukai — bukan hanya mengisi jadwal dengan tugas akademik semata. Keseimbangan ini adalah kunci untuk menjaga produktivitas sekaligus kesehatan mental dalam jangka panjang.
TXT,
    ],
    [
        'slug' => 'psych_08',
        'category' => 'Tidur & Kesehatan',
        'title' => 'Pentingnya Tidur Berkualitas bagi Kesehatan Mental',
        'tags' => 'tidur, insomnia, kesehatan mental, pola hidup',
        'content' => <<<TXT
Begadang mengerjakan tugas atau larut dalam scrolling media sosial hingga dini hari sudah menjadi kebiasaan yang umum di kalangan mahasiswa. Namun kurang tidur secara terus-menerus memiliki dampak yang jauh lebih besar dari sekadar rasa kantuk di siang hari — ia berkaitan erat dengan meningkatnya risiko kecemasan, depresi, dan menurunnya kemampuan mengelola emosi.

Saat tidur, otak melakukan proses konsolidasi memori dan "membersihkan" residu aktivitas mental sepanjang hari. Ketika proses ini terganggu akibat kurang tidur, kemampuan berpikir jernih, mengambil keputusan, dan mengendalikan reaksi emosional pun ikut menurun — membuat masalah kecil terasa jauh lebih berat dari yang sebenarnya.

Beberapa kebiasaan yang dapat membantu meningkatkan kualitas tidur antara lain menetapkan jam tidur dan bangun yang konsisten setiap hari, menghindari layar gawai setidaknya 30 menit sebelum tidur, serta menciptakan suasana kamar yang nyaman dan minim cahaya. Menghindari kafein di sore hari juga cukup berpengaruh bagi banyak orang.

Jika kesulitan tidur terus berlanjut meski sudah mencoba berbagai cara, atau disertai dengan kecemasan yang berlebihan menjelang tidur, ada baiknya mencatatnya di Diary untuk melihat polanya, dan mempertimbangkan konsultasi dengan konselor kampus untuk mendapatkan strategi yang lebih sesuai dengan kondisimu.
TXT,
    ],
    [
        'slug' => 'psych_09',
        'category' => 'Resiliensi',
        'title' => 'Membangun Resiliensi Mental di Masa Perkuliahan',
        'tags' => 'resiliensi, ketahanan mental, adaptasi, mahasiswa',
        'content' => <<<TXT
Resiliensi mental adalah kemampuan untuk bangkit kembali setelah menghadapi kesulitan, tekanan, atau kegagalan. Bukan berarti seseorang yang resilien tidak pernah merasa terpuruk, melainkan mereka memiliki cara untuk memproses kesulitan tersebut dan tetap melangkah maju tanpa terjebak dalam keputusasaan yang berkepanjangan.

Masa kuliah penuh dengan situasi yang menuntut resiliensi — nilai yang tidak sesuai harapan, kegagalan dalam organisasi, atau perubahan rencana hidup yang mendadak. Mahasiswa yang memiliki resiliensi baik cenderung melihat kegagalan sebagai bagian dari proses belajar, bukan sebagai bukti bahwa dirinya tidak mampu.

Resiliensi bukanlah bakat bawaan, melainkan keterampilan yang bisa dilatih. Beberapa caranya antara lain membiasakan diri melihat situasi dari berbagai sudut pandang, tidak menghindar dari masalah namun menghadapinya secara bertahap, serta membangun hubungan sosial yang suportif sebagai tempat berbagi ketika keadaan sulit.

Refleksi diri secara rutin, misalnya lewat kebiasaan menulis Diary, juga membantu mengenali pola pikir yang mungkin memperberat suatu masalah, sehingga secara perlahan kamu bisa belajar merespons kesulitan dengan cara yang lebih sehat dan konstruktif.
TXT,
    ],
    [
        'slug' => 'psych_10',
        'category' => 'Media Sosial',
        'title' => 'Dampak Media Sosial terhadap Kesehatan Mental Mahasiswa',
        'tags' => 'media sosial, perbandingan sosial, kesehatan mental, digital'    ,
        'content' => <<<TXT
Media sosial telah menjadi bagian tak terpisahkan dari kehidupan mahasiswa masa kini, mulai dari mencari informasi kuliah hingga menjaga hubungan sosial. Namun di balik manfaatnya, penggunaan media sosial yang berlebihan juga memiliki kaitan dengan meningkatnya perasaan cemas, rendah diri, dan kesepian pada sebagian penggunanya.

Salah satu penyebab utamanya adalah kecenderungan membandingkan diri dengan kehidupan orang lain yang terlihat "sempurna" di media sosial — padahal apa yang ditampilkan seringkali hanya potongan terbaik dari kehidupan seseorang, bukan gambaran utuh. Perbandingan semacam ini dapat perlahan mengikis rasa syukur dan kepercayaan diri.

Selain itu, notifikasi yang terus-menerus dan kebiasaan scrolling tanpa henti juga dapat mengganggu fokus belajar dan kualitas tidur, yang pada akhirnya turut memengaruhi kondisi mental secara keseluruhan.

Bukan berarti media sosial harus dihindari sepenuhnya, namun penggunaannya perlu lebih disadari — misalnya dengan menetapkan waktu khusus untuk membuka media sosial, membatasi akun yang justru memicu perasaan negatif, dan lebih banyak menghabiskan waktu untuk interaksi langsung yang lebih bermakna secara emosional.
TXT,
    ],
    [
        'slug' => 'psych_11',
        'category' => 'Dukungan Sosial',
        'title' => 'Cara Mendukung Teman yang Sedang Berjuang Secara Mental',
        'tags' => 'dukungan teman, empati, kesehatan mental, sosial',
        'content' => <<<TXT
Terkadang, dukungan yang paling berarti bagi seseorang yang sedang berjuang dengan kesehatan mentalnya bukan datang dari konselor atau profesional, melainkan dari teman dekat yang mau mendengarkan tanpa menghakimi. Namun banyak yang merasa bingung harus berbuat apa ketika melihat temannya tampak kesulitan.

Langkah pertama yang paling sederhana namun sering dilupakan adalah benar-benar mendengarkan. Tidak perlu terburu-buru memberi solusi atau nasihat — cukup hadir, memberi ruang bagi teman untuk bercerita, dan menunjukkan bahwa perasaannya valid untuk dirasakan.

Hindari kalimat yang meremehkan seperti "yang lain juga mengalami hal yang sama, kok" atau "coba lebih bersyukur", karena kalimat semacam ini justru bisa membuat seseorang merasa perasaannya tidak dianggap penting. Sebaliknya, kalimat seperti "aku di sini kalau kamu butuh bicara" atau "terima kasih sudah cerita ke aku" dapat memberi rasa aman yang jauh lebih besar.

Jika kondisi temanmu tampak cukup berat — misalnya menyebutkan keinginan menyakiti diri sendiri — jangan mencoba menanganinya sendirian. Dorong dengan lembut agar ia mau berbicara dengan konselor kampus, dan jika situasinya mendesak, segera hubungi bantuan profesional atau layanan darurat terdekat.
TXT,
    ],
    [
        'slug' => 'psych_12',
        'category' => 'Mindfulness',
        'title' => 'Mindfulness: Latihan Sederhana untuk Hidup di Saat Ini',
        'tags' => 'mindfulness, kesadaran diri, ketenangan, meditasi',
        'content' => <<<TXT
Mindfulness, atau kesadaran penuh, adalah praktik memperhatikan apa yang sedang terjadi di saat ini — baik pikiran, perasaan, maupun sensasi tubuh — tanpa menghakiminya sebagai baik atau buruk. Alih-alih terjebak dalam penyesalan masa lalu atau kekhawatiran akan masa depan, mindfulness mengajak kita untuk sepenuhnya hadir di masa sekarang.

Penelitian di bidang psikologi menunjukkan bahwa praktik mindfulness yang dilakukan secara rutin dapat membantu menurunkan tingkat stres dan kecemasan, meningkatkan fokus, serta membantu seseorang merespons situasi sulit dengan lebih tenang alih-alih bereaksi secara impulsif.

Praktik mindfulness tidak harus rumit. Cara paling sederhana adalah meluangkan beberapa menit untuk duduk tenang sambil memperhatikan napas masuk dan keluar, membiarkan pikiran yang muncul datang dan pergi tanpa perlu mengikutinya. Makan dengan penuh perhatian — benar-benar merasakan tekstur dan rasa makanan tanpa distraksi gawai — juga merupakan bentuk latihan mindfulness sehari-hari.

Membangun kebiasaan mindfulness membutuhkan konsistensi, bukan kesempurnaan. Memulai dengan lima menit sehari jauh lebih berkelanjutan dibanding memaksakan sesi panjang yang justru terasa membebani. Fitur Self Help pada aplikasi ini menyediakan latihan sederhana yang bisa menjadi titik awal untuk membangun kebiasaan ini.
TXT,
    ],
    [
        'slug' => 'psych_13',
        'category' => 'Terapi Kognitif',
        'title' => 'Mengelola Pikiran Negatif dengan Pendekatan CBT',
        'tags' => 'cbt, pikiran negatif, terapi kognitif, distorsi kognitif',
        'content' => <<<TXT
Cognitive Behavioral Therapy (CBT) adalah salah satu pendekatan psikologis yang paling banyak diteliti dan terbukti efektif dalam menangani berbagai masalah kesehatan mental, mulai dari kecemasan hingga depresi. Prinsip dasarnya sederhana: cara kita berpikir tentang suatu kejadian akan memengaruhi perasaan dan perilaku kita terhadap kejadian tersebut.

Salah satu konsep penting dalam CBT adalah distorsi kognitif — pola pikir yang tidak akurat namun terasa begitu meyakinkan, seperti "berpikir semua-atau-tidak-sama-sekali" (menganggap sesuatu gagal total hanya karena tidak sempurna) atau "membaca pikiran" (meyakini orang lain menilai kita secara negatif tanpa bukti yang jelas).

Langkah awal dalam menerapkan prinsip CBT secara mandiri adalah dengan mengenali pikiran otomatis yang muncul saat menghadapi situasi sulit, lalu mempertanyakan pikiran tersebut secara objektif — apakah ada bukti yang mendukung, atau apakah ada cara pandang lain yang lebih realistis? Struktur inilah yang menjadi dasar dari kolom "Situasi" dan "Pikiran Pertama" pada fitur Diary Terstruktur di aplikasi ini.

Meskipun prinsip dasar CBT bisa dipelajari dan dilatih secara mandiri, penanganan menyeluruh untuk masalah yang lebih kompleks tetap memerlukan pendampingan dari konselor atau psikolog profesional yang terlatih dalam pendekatan ini.
TXT,
    ],
    [
        'slug' => 'psych_14',
        'category' => 'Support System',
        'title' => 'Pentingnya Support System dalam Menjaga Kesehatan Mental',
        'tags' => 'support system, keluarga, teman, komunitas',
        'content' => <<<TXT
Tidak ada seorang pun yang bisa menjaga kesehatan mentalnya sendirian sepenuhnya. Support system — jaringan dukungan yang terdiri dari keluarga, teman, komunitas, hingga tenaga profesional — memainkan peran penting dalam membantu seseorang menghadapi masa-masa sulit dan menjaga kestabilan emosional dalam jangka panjang.

Memiliki support system yang sehat bukan berarti harus mengenal banyak orang, melainkan memiliki setidaknya beberapa hubungan yang terasa aman untuk menjadi diri sendiri, berbagi kesulitan tanpa takut dihakimi, dan mendapatkan dukungan yang tulus tanpa syarat.

Bagi mahasiswa yang merantau jauh dari keluarga, membangun support system baru di lingkungan kampus menjadi tantangan tersendiri. Bergabung dengan komunitas atau organisasi yang sesuai minat, menjaga komunikasi rutin dengan keluarga meski melalui jarak jauh, serta terbuka untuk membangun pertemanan baru adalah beberapa langkah yang bisa membantu.

Konselor kampus juga merupakan bagian penting dari support system yang seringkali terlupakan. Berbeda dengan teman atau keluarga, konselor memberikan ruang yang netral dan profesional untuk membahas masalah tanpa memengaruhi hubungan personal — sebuah bentuk dukungan yang sama pentingnya dengan dukungan dari orang-orang terdekat.
TXT,
    ],
    [
        'slug' => 'psych_15',
        'category' => 'Bantuan Profesional',
        'title' => 'Kapan Harus Mencari Bantuan Profesional?',
        'tags' => 'bantuan profesional, konselor, psikolog, kapan konsultasi',
        'content' => <<<TXT
Salah satu pertanyaan yang sering membuat ragu adalah: seberapa "parah" suatu masalah sampai layak dibawa ke konselor atau psikolog? Banyak orang menunda mencari bantuan karena merasa masalahnya "belum cukup besar", padahal semakin cepat suatu kondisi ditangani, semakin besar pula peluang untuk pulih dengan baik.

Beberapa tanda yang menunjukkan sudah saatnya mencari bantuan profesional antara lain: perasaan sedih, cemas, atau hampa yang berlangsung lebih dari dua minggu, kesulitan menjalankan aktivitas sehari-hari seperti biasanya, perubahan drastis pada pola tidur atau makan, menarik diri dari lingkungan sosial, serta munculnya pikiran untuk menyakiti diri sendiri.

Penting untuk diingat bahwa mencari bantuan profesional bukanlah tanda kegagalan atau kelemahan. Sebaliknya, ini adalah bentuk tanggung jawab terhadap diri sendiri — sama seperti memeriksakan diri ke dokter ketika demam tak kunjung reda. Konselor terlatih untuk membantu tanpa menghakimi, dan segala yang dibicarakan bersifat rahasia.

Di aplikasi ini, kamu bisa memulai dengan mengisi Self-Assessment untuk mendapatkan gambaran awal kondisimu, lalu memilih konselor kampus yang tersedia untuk mengajukan booking konsultasi. Tidak perlu menunggu sampai kondisi memburuk — semakin awal langkah ini diambil, semakin ringan pula proses pemulihannya.
TXT,
    ],
];

$mysqli->query('DELETE FROM articles');
$mysqli->query('ALTER TABLE articles AUTO_INCREMENT = 1');

$stmt = $mysqli->prepare(
    'INSERT INTO articles (admin_id, user_id, title, content, category, tags, image, published_at, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
);

$count = 0;
foreach ($articles as $i => $a) {
    $image = '/uploads/articles/' . $a['slug'] . '.jpg';
    $content = trim($a['content']);
    // Sebar tanggal publikasi mundur beberapa hari supaya urutan "terbaru" bervariasi.
    $publishedAt = date('Y-m-d H:i:s', strtotime('-' . ((count($articles) - $i) * 2) . ' days'));

    $stmt->bind_param(
        'iissssss',
        $authorId,
        $authorId,
        $a['title'],
        $content,
        $a['category'],
        $a['tags'],
        $image,
        $publishedAt
    );
    $stmt->execute();
    $count++;
    echo "  + {$a['title']}\n";
}

echo "Selesai: {$count} artikel psikologi berhasil ditambahkan.\n";
