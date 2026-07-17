<?php

namespace App\Services;

/**
 * Pure scoring logic for the two self-assessment instruments (BDI-II, PWB/Ryff-18).
 * No DB access — takes answered questions/choices, returns totals + category + feedback text.
 */
class AssessmentScoringService
{
    public const BDI2_MAX_SCORE = 63;
    public const PWB_MAX_SCORE = 108;
    public const PWB_DIMENSION_MAX_SCORE = 18;

    public const PWB_DIMENSIONS = [
        'self_acceptance'      => 'Penerimaan Diri',
        'positive_relations'   => 'Hubungan Positif dengan Orang Lain',
        'autonomy'              => 'Otonomi',
        'environmental_mastery' => 'Penguasaan Lingkungan',
        'purpose_in_life'       => 'Tujuan Hidup',
        'personal_growth'       => 'Pertumbuhan Pribadi',
    ];

    private const BDI2_FEEDBACK = [
        'Minimal' => 'Berdasarkan hasil assessment, tidak ditemukan indikasi gejala depresi yang bermakna. Tetap jaga pola hidup sehat, istirahat yang cukup, serta pertahankan aktivitas yang mendukung kesehatan mental.',
        'Ringan'  => 'Anda menunjukkan gejala depresi ringan. Kondisi ini dapat dipengaruhi oleh tekanan akademik, pekerjaan, maupun masalah pribadi. Cobalah menjaga keseimbangan antara aktivitas dan waktu istirahat, berbagi cerita dengan orang yang dipercaya, serta menerapkan strategi pengelolaan stres.',
        'Sedang'  => 'Hasil assessment menunjukkan adanya gejala depresi pada tingkat sedang. Apabila kondisi ini dirasakan cukup mengganggu aktivitas sehari-hari atau berlangsung dalam waktu yang lama, disarankan untuk berkonsultasi dengan konselor atau psikolog agar memperoleh pendampingan yang sesuai.',
        'Berat'   => 'Hasil assessment menunjukkan gejala depresi pada tingkat berat. Assessment ini bukan merupakan diagnosis, namun hasil ini menunjukkan bahwa Anda sebaiknya segera berkonsultasi dengan psikolog, psikiater, atau tenaga kesehatan mental profesional untuk memperoleh evaluasi dan bantuan lebih lanjut.',
    ];

    private const BDI2_TIPS = [
        'Jaga pola tidur yang teratur, usahakan tidur dan bangun di jam yang sama setiap hari.',
        'Konsumsi makanan bergizi seimbang dan cukupi kebutuhan cairan tubuh.',
        'Tetap aktif secara fisik, misalnya jalan kaki atau olahraga ringan secara rutin.',
        'Luangkan waktu untuk aktivitas yang Anda nikmati dan istirahat yang cukup.',
        'Tetap terhubung dengan keluarga, teman, atau orang-orang terdekat.',
    ];

    private const PWB_OVERALL_FEEDBACK = [
        'Tinggi' => 'Secara umum, hasil assessment menunjukkan bahwa tingkat kesejahteraan psikologis Anda berada pada kategori tinggi. Hal ini menunjukkan bahwa Anda mampu menjalani kehidupan dengan cukup baik pada berbagai aspek psikologis. Tetap pertahankan kebiasaan positif yang telah dilakukan dan terus jaga keseimbangan antara kesehatan fisik, mental, serta hubungan sosial.',
        'Sedang' => 'Secara umum, tingkat kesejahteraan psikologis Anda berada pada kategori sedang. Anda telah memiliki berbagai aspek psikologis yang cukup baik, namun masih terdapat beberapa area yang dapat dikembangkan. Fokuslah pada dimensi dengan skor terendah sebagai langkah awal untuk meningkatkan kesejahteraan psikologis secara menyeluruh.',
        'Rendah' => 'Secara umum, hasil assessment menunjukkan bahwa tingkat kesejahteraan psikologis Anda berada pada kategori rendah. Hasil ini bukan merupakan diagnosis gangguan psikologis, melainkan gambaran kondisi psikologis berdasarkan jawaban yang Anda berikan saat ini. Apabila Anda merasa mengalami kesulitan yang berlangsung cukup lama atau mengganggu aktivitas sehari-hari, disarankan untuk berdiskusi dengan konselor, psikolog, atau tenaga profesional yang kompeten.',
    ];

    private const PWB_DIMENSION_FEEDBACK = [
        'self_acceptance' => [
            'Tinggi' => 'Anda menunjukkan kemampuan yang baik dalam menerima diri sendiri, baik kelebihan maupun kekurangan yang dimiliki. Anda juga cenderung mampu memandang pengalaman hidup sebagai bagian dari proses pembelajaran dan perkembangan diri. Pertahankan sikap positif ini sebagai bekal dalam menghadapi berbagai tantangan kehidupan.',
            'Sedang' => 'Anda sudah memiliki penerimaan diri yang cukup baik, namun pada situasi tertentu masih mungkin merasa kurang puas terhadap diri sendiri atau pencapaian yang telah diraih. Cobalah lebih sering mengapresiasi proses dan kemajuan yang telah Anda lakukan daripada hanya berfokus pada kekurangan.',
            'Rendah' => 'Hasil menunjukkan bahwa Anda mungkin masih sering merasa kurang puas terhadap diri sendiri atau pencapaian hidup. Cobalah mulai mengenali kelebihan yang dimiliki, menerima kekurangan sebagai bagian dari proses berkembang, serta jangan ragu mencari dukungan dari orang terdekat apabila diperlukan.',
        ],
        'positive_relations' => [
            'Tinggi' => 'Anda memiliki kemampuan yang baik dalam membangun hubungan yang hangat, saling percaya, dan saling mendukung dengan orang lain. Kemampuan ini merupakan salah satu faktor penting dalam menjaga kesejahteraan psikologis. Terus pertahankan komunikasi yang sehat dengan lingkungan sekitar.',
            'Sedang' => 'Anda mampu menjalin hubungan dengan orang lain, namun dalam beberapa situasi mungkin masih merasa kesulitan untuk terbuka atau membangun kedekatan. Luangkan waktu untuk memperkuat komunikasi dan menjaga hubungan yang positif dengan orang-orang di sekitar Anda.',
            'Rendah' => 'Anda mungkin sedang mengalami kesulitan dalam membangun hubungan yang dekat atau merasa kurang memiliki dukungan sosial. Cobalah mulai membuka komunikasi dengan orang yang dipercaya, karena memiliki hubungan sosial yang sehat dapat membantu menjaga kesejahteraan psikologis.',
        ],
        'autonomy' => [
            'Tinggi' => 'Anda menunjukkan kemampuan yang baik dalam mengambil keputusan secara mandiri serta tidak mudah terpengaruh oleh tekanan atau pendapat orang lain. Sikap ini membantu Anda tetap konsisten terhadap nilai dan tujuan yang dimiliki.',
            'Sedang' => 'Anda sudah cukup mampu mengambil keputusan sendiri, namun pada kondisi tertentu masih mungkin dipengaruhi oleh pendapat atau tekanan dari lingkungan sekitar. Tetaplah mempertimbangkan masukan orang lain tanpa mengabaikan keyakinan dan nilai yang Anda miliki.',
            'Rendah' => 'Hasil menunjukkan bahwa Anda mungkin masih sering bergantung pada penilaian atau pendapat orang lain dalam mengambil keputusan. Cobalah melatih kepercayaan diri dan biasakan mengambil keputusan berdasarkan pertimbangan pribadi yang matang.',
        ],
        'environmental_mastery' => [
            'Tinggi' => 'Anda mampu mengelola aktivitas, tanggung jawab, dan tantangan kehidupan sehari-hari dengan baik. Kemampuan ini menunjukkan bahwa Anda cukup adaptif dalam menghadapi perubahan maupun tekanan yang muncul.',
            'Sedang' => 'Anda sudah cukup mampu mengatur aktivitas dan tanggung jawab sehari-hari, namun pada kondisi tertentu mungkin masih merasa kewalahan menghadapi berbagai tuntutan. Menyusun prioritas dan mengatur waktu dapat membantu meningkatkan kemampuan ini.',
            'Rendah' => 'Anda mungkin sedang mengalami kesulitan dalam mengelola berbagai tuntutan kehidupan sehari-hari sehingga lebih mudah merasa tertekan. Cobalah membagi tugas menjadi langkah-langkah kecil, membuat jadwal yang teratur, dan meminta bantuan apabila diperlukan.',
        ],
        'purpose_in_life' => [
            'Tinggi' => 'Anda memiliki arah dan tujuan hidup yang jelas serta memahami hal-hal yang ingin dicapai di masa depan. Memiliki tujuan hidup yang kuat dapat menjadi sumber motivasi dalam menghadapi berbagai tantangan.',
            'Sedang' => 'Anda telah memiliki beberapa tujuan dalam hidup, namun mungkin masih belum sepenuhnya yakin terhadap arah yang ingin dicapai. Luangkan waktu untuk mengevaluasi kembali prioritas dan target pribadi agar lebih terarah.',
            'Rendah' => 'Hasil menunjukkan bahwa Anda mungkin masih merasa bingung mengenai tujuan atau arah hidup yang ingin dicapai. Cobalah mulai menetapkan tujuan sederhana yang realistis dan sesuai dengan nilai-nilai yang Anda anggap penting.',
        ],
        'personal_growth' => [
            'Tinggi' => 'Anda menunjukkan keinginan yang kuat untuk terus belajar, berkembang, dan meningkatkan kualitas diri. Sikap terbuka terhadap pengalaman baru merupakan modal penting dalam perkembangan pribadi.',
            'Sedang' => 'Anda memiliki keinginan untuk berkembang, namun pada kondisi tertentu mungkin merasa ragu untuk mencoba hal baru atau keluar dari zona nyaman. Teruslah membuka diri terhadap pengalaman yang dapat memperkaya kemampuan dan wawasan Anda.',
            'Rendah' => 'Anda mungkin sedang merasa kurang termotivasi untuk berkembang atau melakukan perubahan dalam hidup. Mulailah dengan menetapkan target kecil yang dapat dicapai secara bertahap agar proses pengembangan diri terasa lebih ringan dan bermakna.',
        ],
    ];

    /**
     * @param array<int, array{question_id:int, choice_id:int, score_value:int}> $answers
     * @return array{total_score:int, max_score:int, category:string, feedback:string, tips:array}
     */
    public function scoreBdi2(array $answers): array
    {
        $total = array_sum(array_column($answers, 'score_value'));
        $category = $this->bdi2Category($total);

        return [
            'total_score' => $total,
            'max_score'   => self::BDI2_MAX_SCORE,
            'category'    => $category,
            'feedback'    => self::BDI2_FEEDBACK[$category],
            'tips'        => $category === 'Minimal' ? self::BDI2_TIPS : [],
        ];
    }

    public function bdi2Category(int $total): string
    {
        if ($total <= 13) {
            return 'Minimal';
        }
        if ($total <= 19) {
            return 'Ringan';
        }
        if ($total <= 28) {
            return 'Sedang';
        }

        return 'Berat';
    }

    public function bdi2Feedback(string $category): string
    {
        return self::BDI2_FEEDBACK[$category] ?? '';
    }

    public function bdi2Tips(string $category): array
    {
        return $category === 'Minimal' ? self::BDI2_TIPS : [];
    }

    /**
     * @param array<int, array{question_id:int, choice_id:int, score_value:int, dimension:?string, is_reverse_scored:bool}> $answers
     *        score_value here is the *raw* Likert value chosen (1-6); reverse scoring is applied inside this method.
     * @return array{total_score:int, max_score:int, category:string, percentage:float, feedback:string, dimension_scores:array, scored_answers:array}
     */
    public function scorePwb(array $answers): array
    {
        $dimensionTotals = array_fill_keys(array_keys(self::PWB_DIMENSIONS), 0);
        $total = 0;
        $scoredAnswers = [];

        foreach ($answers as $answer) {
            $raw = (int) $answer['score_value'];
            $scored = !empty($answer['is_reverse_scored']) ? (7 - $raw) : $raw;
            $total += $scored;

            $dimension = $answer['dimension'] ?? null;
            if ($dimension !== null && isset($dimensionTotals[$dimension])) {
                $dimensionTotals[$dimension] += $scored;
            }

            $scoredAnswers[] = [
                'question_id' => $answer['question_id'],
                'choice_id'   => $answer['choice_id'],
                'score_value' => $scored,
            ];
        }

        $percentage = round(($total / self::PWB_MAX_SCORE) * 100, 2);
        $category = $this->pwbCategoryFromPercentage($percentage);

        $dimensionScores = [];
        foreach ($dimensionTotals as $key => $score) {
            $dimPercentage = round(($score / self::PWB_DIMENSION_MAX_SCORE) * 100, 2);
            $dimCategory = $this->pwbCategoryFromPercentage($dimPercentage);
            $dimensionScores[$key] = [
                'label'      => self::PWB_DIMENSIONS[$key],
                'score'      => $score,
                'max_score'  => self::PWB_DIMENSION_MAX_SCORE,
                'percentage' => $dimPercentage,
                'category'   => $dimCategory,
                'feedback'   => self::PWB_DIMENSION_FEEDBACK[$key][$dimCategory],
            ];
        }

        return [
            'total_score'      => $total,
            'max_score'        => self::PWB_MAX_SCORE,
            'percentage'       => $percentage,
            'category'         => $category,
            'feedback'         => self::PWB_OVERALL_FEEDBACK[$category],
            'dimension_scores' => $dimensionScores,
            'scored_answers'   => $scoredAnswers,
        ];
    }

    public function pwbCategoryFromPercentage(float $percentage): string
    {
        if ($percentage >= 80) {
            return 'Tinggi';
        }
        if ($percentage >= 60) {
            return 'Sedang';
        }

        return 'Rendah';
    }

    public function pwbOverallFeedback(string $category): string
    {
        return self::PWB_OVERALL_FEEDBACK[$category] ?? '';
    }

    public function pwbDimensionFeedback(string $dimension, string $category): string
    {
        return self::PWB_DIMENSION_FEEDBACK[$dimension][$category] ?? '';
    }

    public function pwbDimensionLabel(string $dimension): string
    {
        return self::PWB_DIMENSIONS[$dimension] ?? $dimension;
    }

    // Combined PWB + BDI-II classification: each category pair collapses to one of
    // 6 risk levels, which in turn drives a single system recommendation. The level
    // is the two categories' severity ranks summed — worse wellbeing and worse
    // depression score both push the level up.
    private const PWB_RISK_RANK = [
        'Tinggi' => 0,
        'Sedang' => 1,
        'Rendah' => 2,
    ];

    private const BDI2_SEVERITY_RANK = [
        'Minimal' => 0,
        'Ringan'  => 1,
        'Sedang'  => 2,
        'Berat'   => 3,
    ];

    private const COMBINED_LEVELS = [
        1 => [
            'risk_label'     => 'Sangat Rendah',
            'recommendation' => 'Mental Health Education',
            'features'       => 'Artikel edukasi & tips menjaga kesehatan mental',
            'purpose'        => 'Mempertahankan kondisi psikologis yang sehat',
        ],
        2 => [
            'risk_label'     => 'Rendah',
            'recommendation' => 'Self Help Dasar',
            'features'       => 'Halaman Self Help (isi: materi, breathing exercise sederhana, tips tidur, aktivitas positif)',
            'purpose'        => 'Mencegah kondisi berkembang menjadi lebih buruk',
        ],
        3 => [
            'risk_label'     => 'Rendah–Sedang',
            'recommendation' => 'Self Help',
            'features'       => 'Self Help + Diary Terstruktur',
            'purpose'        => 'Membantu pengguna mengenali emosi dan meningkatkan coping skill',
        ],
        4 => [
            'risk_label'     => 'Sedang',
            'recommendation' => 'Self Help + Monitoring',
            'features'       => 'Self Help + Diary Terstruktur (+Gratitude Journal) + Anjuran mengisi assessment kembali',
            'purpose'        => 'Membantu mengurangi distress dan memonitor perkembangan kondisi',
        ],
        5 => [
            'risk_label'     => 'Sedang–Tinggi',
            'recommendation' => 'Self Help Intensif',
            'features'       => 'Self Help (materi lebih lengkap) + Diary Terstruktur (+Gratitude Journal + Self Reflection)',
            'purpose'        => 'Menstabilkan kondisi emosional dan mencegah perburukan',
        ],
        6 => [
            'risk_label'     => 'Tinggi',
            'recommendation' => 'Self Help + Psychological First Aid (PFA)',
            'features'       => 'Self Help + Halaman PFA',
            'purpose'        => 'Memberikan dukungan psikologis awal sebelum bantuan profesional',
        ],
    ];

    /**
     * @return array{level:int, risk_label:string, recommendation:string, features:string, purpose:string}
     */
    public function combinedLevel(string $pwbCategory, string $bdi2Category): array
    {
        $pwbRank = self::PWB_RISK_RANK[$pwbCategory] ?? 1;
        $bdi2Rank = self::BDI2_SEVERITY_RANK[$bdi2Category] ?? 0;
        $level = $pwbRank + $bdi2Rank + 1;

        return array_merge(['level' => $level], self::COMBINED_LEVELS[$level]);
    }
}
