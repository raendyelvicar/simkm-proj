<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\ActivityPlan;
use App\Models\CounselingBooking;
use App\Models\DiaryEntry;
use App\Repositories\CounselorRepository;
use App\Repositories\ReportRepository;
use App\Services\AssessmentScoringService;
use App\Services\EngagementScoringService;
use App\Services\ReportPdfService;

/**
 * The 8 Laporan report pages. One controller for the whole "Laporan" feature area,
 * matching the app's one-controller-per-feature convention. Each report has a private
 * `xxxData()` method that fetches + shapes the data (reused by both the HTML view and
 * the PDF export), a public `xxx()` view action, and a public `xxxPdf()` export action.
 */
class ReportController
{
    private const DIARY_PER_PAGE = 10;
    private const REPORT_PER_PAGE = 10;

    private ReportRepository $laporan;
    private CounselorRepository $counselors;
    private AssessmentScoringService $scoring;
    private EngagementScoringService $engagementScoring;
    private ReportPdfService $pdf;
    private ?array $counselorProfile = null;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->laporan = new ReportRepository();
        $this->counselors = new CounselorRepository();
        $this->scoring = new AssessmentScoringService();
        $this->engagementScoring = new EngagementScoringService();
        $this->pdf = new ReportPdfService();
    }

    // GET /laporan — hub listing only the report cards this role may open.
    public function index(Request $request): void
    {
        $role = $this->role();

        $cards = [
            ['slug' => 'self-assessment', 'icon' => '📝', 'title' => 'Riwayat Self Assessment', 'desc' => 'Skor PWB & BDI-II, tingkat risiko, dan rekomendasi sistem.', 'roles' => ['student', 'counselor', 'admin']],
            ['slug' => 'diary', 'icon' => '📖', 'title' => 'Diary', 'desc' => 'Riwayat diary terstruktur student.', 'roles' => ['student', 'counselor', 'admin']],
            ['slug' => 'self-help', 'icon' => '🌱', 'title' => 'Aktivitas Self Help', 'desc' => 'Aktivitas positif yang direncanakan & diselesaikan.', 'roles' => ['student', 'counselor', 'admin']],
            ['slug' => 'konseling', 'icon' => '💬', 'title' => 'Konseling', 'desc' => 'Riwayat booking dan sesi konseling.', 'roles' => ['student', 'counselor', 'admin']],
            ['slug' => 'risk-mapping', 'icon' => '📊', 'title' => 'Pemetaan Risiko Kesehatan Mental', 'desc' => 'Distribusi tingkat risiko student.', 'roles' => ['counselor', 'admin']],
            ['slug' => 'mood-analysis', 'icon' => '📈', 'title' => 'Analisis Mood & Perkembangan Kondisi', 'desc' => 'Perbandingan assessment awal vs. terakhir.', 'roles' => ['student', 'counselor', 'admin']],
            ['slug' => 'engagement', 'icon' => '✅', 'title' => 'Evaluasi Keterlibatan Mahasiswa', 'desc' => 'Tingkat keaktifan mahasiswa menggunakan aplikasi.', 'roles' => ['counselor', 'admin']],
            ['slug' => 'counselor-activity', 'icon' => '🧑‍⚕️', 'title' => 'Aktivitas Konselor', 'desc' => 'Jumlah sesi, fakultas, dan risiko terbanyak yang ditangani per konselor.', 'roles' => ['counselor', 'admin']],
        ];

        Response::view('laporan/index', [
            'title' => 'Laporan',
            'cards' => array_values(array_filter($cards, fn ($c) => in_array($role, $c['roles'], true))),
        ]);
    }

    // --- 1. Riwayat Self Assessment --------------------------------------------------

    private const SELF_ASSESSMENT_SORTABLE = [
        'name'       => 'name',
        'date'    => 'date',
        'pwb_score'  => 'pwb_score',
        'bdi2_score' => 'bdi2_score',
        'risk_level' => 'risk_level',
    ];

    public function selfAssessment(Request $request): void
    {
        $data = $this->selfAssessmentData($request);
        $sort = (string) $request->get('sort', 'date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $rows = $this->sortRows($data['rows'], $sort, $dir, self::SELF_ASSESSMENT_SORTABLE);
        $p = $this->paginateRows($rows, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/self_assessment', array_merge($data, [
            'title'      => 'Laporan Riwayat Self Assessment',
            'rows'       => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function selfAssessmentPdf(Request $request): void
    {
        $data = $this->selfAssessmentData($request);

        $table = $this->tableHtml(
            ['Name', 'Date', 'Skor PWB', 'Category PWB', 'Skor BDI-II', 'Category BDI-II', 'Tingkat Risiko', 'Recommendation'],
            $data['rows'],
            fn ($r) => [
                htmlspecialchars($r['name']),
                $r['date'] ? htmlspecialchars(date('d M Y', strtotime($r['date']))) : '-',
                $r['pwb_score'] ?? '-',
                htmlspecialchars($r['pwb_category'] ?? '-'),
                $r['bdi2_score'] ?? '-',
                htmlspecialchars($r['bdi2_category'] ?? '-'),
                htmlspecialchars($r['risk_label']),
                htmlspecialchars($r['recommendation']),
            ]
        );

        $this->streamPdf('Laporan Riwayat Self Assessment', 'self_assessment', $data['filters'], $table);
    }

    private function selfAssessmentData(Request $request): array
    {
        $filters = $this->applyScope($this->commonFilters($request), 'self-assessment');
        $sessions = $this->laporan->selfAssessmentSessions($filters);

        $rows = array_map(function ($s) {
            $pwbCat = $s['pwb']['category'] ?? null;
            $bdi2Cat = $s['bdi2']['category'] ?? null;
            $risk = ($pwbCat && $bdi2Cat) ? $this->scoring->combinedLevel($pwbCat, $bdi2Cat) : null;

            return [
                'name'           => $s['name'],
                'student_number'            => $s['student_number'],
                'date'        => $s['date'],
                'pwb_score'      => $s['pwb']['total_score'] ?? null,
                'pwb_category'   => $pwbCat,
                'bdi2_score'     => $s['bdi2']['total_score'] ?? null,
                'bdi2_category'  => $bdi2Cat,
                'risk_level'     => $risk['level'] ?? null,
                'risk_label'     => $risk['risk_label'] ?? '-',
                'recommendation' => $risk['recommendation'] ?? '-',
            ];
        }, array_reverse($sessions));

        return ['rows' => $rows, 'filters' => $filters];
    }

    // --- 2. Diary ----------------------------------------------------------------------

    private const DIARY_SORTABLE = [
        'entry_date'   => 'entry_date',
        'student_name' => 'student_name',
    ];

    public function diary(Request $request): void
    {
        $data = $this->diaryData($request);
        $sort = (string) $request->get('sort', 'entry_date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        // Charts/stat tiles below still need the full filtered set (computed from
        // $data['rows'] directly in the template), but the "Detail Entri" list is
        // sorted+paginated separately — with hundreds of entries in range, rendering
        // every one as a full card made the page effectively unscrollable.
        $sorted = $this->sortRows($data['rows'], $sort, $dir, self::DIARY_SORTABLE);
        $p = $this->paginateRows($sorted, max(1, (int) $request->get('page', 1)), self::DIARY_PER_PAGE);

        Response::view('laporan/diary', array_merge($data, [
            'title'      => 'Laporan Diary',
            'entries'    => $p['items'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'total'      => $p['total'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function diaryPdf(Request $request): void
    {
        $data = $this->diaryData($request);
        $pages = array_map(fn ($e) => $this->diaryEntryHtml($e), $data['rows']);
        $body = $pages ? implode('<div class="page-break"></div>', $pages) : '<p>Tidak ada diary pada periode ini.</p>';

        $this->streamPdf('Laporan Diary', 'diary', $data['filters'], $body);
    }

    private function diaryData(Request $request): array
    {
        $filters = $this->applyScope($this->commonFilters($request), 'diary');

        $rows = array_map(
            fn ($row) => array_merge((new DiaryEntry($row))->toArray(), [
                'student_name' => $row['student_name'],
                'student_number'  => $row['student_number'],
            ]),
            $this->laporan->diaryRows($filters)
        );

        return ['rows' => $rows, 'filters' => $filters];
    }

    private function diaryEntryHtml(array $e): string
    {
        $row = fn ($label, $value) => '<tr><td class="label">' . htmlspecialchars($label) . '</td><td>'
            . nl2br(htmlspecialchars((string) $value)) . '</td></tr>';

        return '<h2>' . htmlspecialchars($e['student_name']) . ' &mdash; '
            . htmlspecialchars($e['entry_date'] ? date('d M Y', strtotime($e['entry_date'])) : '-') . '</h2>'
            . '<table class="table">'
            . $row('Situation', $e['situation'])
            . $row('Pikiran', $e['initial_thoughts'])
            . $row('Emosi', implode(', ', $e['emotions_list']) . ($e['other_emotions'] ? ', ' . $e['other_emotions'] : ''))
            . $row('Intensitas Emosi', $e['emotion_intensity'] . ' / 5')
            . $row('Reaksi Fisik', implode(', ', $e['physical_reactions_list']) . ($e['other_physical_reactions'] ? ', ' . $e['other_physical_reactions'] : ''))
            . $row('Behavior', $e['behavior'])
            . $row('Self Reflection', $e['self_reflection'] ?? '-')
            . $row('Gratitude Journal', $e['gratitude_list'] ? implode('; ', $e['gratitude_list']) : '-')
            . $row('Rencana Besok', $e['tomorrow_plan'] ?? '-')
            . '</table>';
    }

    // --- 3. Aktivitas Self Help --------------------------------------------------------

    private const SELF_HELP_SORTABLE = [
        'student_name' => 'student_name',
        'title'        => 'title',
        'planned_date' => 'planned_date',
        'status'       => 'status',
    ];

    public function selfHelp(Request $request): void
    {
        $data = $this->selfHelpData($request);
        $sort = (string) $request->get('sort', 'planned_date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $rows = $this->sortRows($data['rows'], $sort, $dir, self::SELF_HELP_SORTABLE);
        $p = $this->paginateRows($rows, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/self_help', array_merge($data, [
            'title'      => 'Laporan Aktivitas Self Help',
            'rows'       => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function selfHelpPdf(Request $request): void
    {
        $data = $this->selfHelpData($request);

        $table = $this->tableHtml(
            ['Name', 'Aktivitas', 'Date', 'Status', 'Mood Sebelum', 'Mood Sesudah'],
            $data['rows'],
            fn ($r) => [
                htmlspecialchars($r['student_name']),
                htmlspecialchars($r['title']),
                $r['planned_date'] ? htmlspecialchars(date('d M Y', strtotime($r['planned_date']))) : '-',
                htmlspecialchars($this->selfHelpStatusLabel($r['status'])),
                $r['mood_before'] ?? '-',
                $r['mood_after'] ?? '-',
            ]
        );

        $this->streamPdf('Laporan Aktivitas Self Help', 'self_help', $data['filters'], $table);
    }

    private function selfHelpData(Request $request): array
    {
        $filters = $this->applyScope($this->commonFilters($request), 'self-help');

        $rows = array_map(
            fn ($row) => array_merge((new ActivityPlan($row))->toArray(), [
                'student_name' => $row['student_name'],
                'student_number'  => $row['student_number'],
            ]),
            $this->laporan->selfHelpRows($filters)
        );

        return ['rows' => $rows, 'filters' => $filters];
    }

    private function selfHelpStatusLabel(string $status): string
    {
        return match ($status) {
            'done'    => 'Selesai',
            'skipped' => 'Dilewati',
            default   => 'Direncanakan',
        };
    }

    // --- 4. Konseling --------------------------------------------------------------------

    private const KONSELING_SORTABLE = [
        'date'       => 'date',
        'student_name'  => 'student_name',
        'counselor_name' => 'counselor_name',
        'status'        => 'status',
    ];

    public function konseling(Request $request): void
    {
        $data = $this->konselingData($request);
        $sort = (string) $request->get('sort', 'date');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $rows = $this->sortRows($data['rows'], $sort, $dir, self::KONSELING_SORTABLE);
        $p = $this->paginateRows($rows, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/konseling', array_merge($data, [
            'title'      => 'Laporan Konseling',
            'rows'       => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function konselingPdf(Request $request): void
    {
        $data = $this->konselingData($request);

        $table = $this->tableHtml(
            ['Tanggal', 'Mahasiswa', 'Konselor', 'Jam', 'Status Booking', 'Catatan'],
            $data['rows'],
            fn ($r) => [
                $r['date'] ? htmlspecialchars(date('d M Y', strtotime($r['date']))) : '-',
                htmlspecialchars($r['student_name']),
                htmlspecialchars($r['counselor_name']),
                htmlspecialchars(substr($r['start_time'], 0, 5) . '-' . substr($r['end_time'], 0, 5)),
                htmlspecialchars($r['status']),
                htmlspecialchars($r['counselor_notes'] ?? '-'),
            ]
        );

        $this->streamPdf('Laporan Konseling', 'konseling', $data['filters'], $table);
    }

    private function konselingData(Request $request): array
    {
        $filters = $this->commonFilters($request);
        $filters['status'] = $request->get('status') ?: null;
        $role = $this->role();

        if ($role === 'student') {
            $filters['user_id'] = (int) $_SESSION['user_id'];
        } elseif ($role === 'counselor') {
            $filters['counselor_id'] = $this->currentCounselorId();
        } else {
            $filters['konselor_search'] = trim((string) $request->get('counselor', ''));
        }

        $rows = array_map(
            fn ($row) => array_merge((new CounselingBooking($row))->toArray(), [
                'student_name'     => $row['student_name'],
                'student_number'      => $row['student_number'],
                'counselor_name'    => $row['counselor_name'],
                'counselor_notes' => $row['counselor_notes'],
                'recommendation'      => $row['recommendation'],
                'follow_up'    => $row['follow_up'],
                'completed_at'     => $row['completed_at'],
            ]),
            $this->laporan->konselingRows($filters)
        );

        return ['rows' => $rows, 'filters' => $filters];
    }

    // --- 5. Pemetaan Risiko Kesehatan Mental --------------------------------------------

    private const RISK_LABEL_ORDER = ['Sangat Rendah', 'Rendah', 'Rendah–Sedang', 'Sedang', 'Sedang–Tinggi', 'Tinggi'];

    public function riskMapping(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        Response::view('laporan/risk_mapping', array_merge($this->riskMappingData($request), ['title' => 'Laporan Pemetaan Risiko Kesehatan Mental']));
    }

    public function riskMappingPdf(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        $data = $this->riskMappingData($request);

        $table = $this->tableHtml(
            ['Tingkat Risiko', 'Jumlah', 'Persentase'],
            $data['distribution'],
            fn ($r) => [htmlspecialchars($r['label']), $r['count'], $r['percentage'] . '%']
        );

        $this->streamPdf('Laporan Pemetaan Risiko Kesehatan Mental', 'risk_mapping', $data['filters'], $table);
    }

    private function riskMappingData(Request $request): array
    {
        $filters = $this->commonFilters($request);
        unset($filters['search']);
        if ($this->role() === 'counselor') {
            $filters['student_ids'] = $this->laporan->counselorStudentIds($this->currentCounselorId());
        }

        $studentRows = $this->laporan->latestRiskCategories($filters);

        $counts = [];
        foreach ($studentRows as $s) {
            $risk = $this->scoring->combinedLevel($s['pwb_category'], $s['bdi2_category']);
            $counts[$risk['risk_label']] = ($counts[$risk['risk_label']] ?? 0) + 1;
        }

        $total = array_sum($counts);
        $distribution = [];
        foreach (self::RISK_LABEL_ORDER as $label) {
            $count = $counts[$label] ?? 0;
            $distribution[] = [
                'label'      => $label,
                'count'      => $count,
                'percentage' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
            ];
        }

        return ['distribution' => $distribution, 'total' => $total, 'filters' => $filters];
    }

    // --- 6. Analisis Mood & Perkembangan Kondisi ----------------------------------------

    private const MOOD_ANALYSIS_SORTABLE = [
        'name'   => 'name',
        'status' => 'status',
    ];

    // $rows stays the full filtered set (the trend chart's "exactly one student in
    // view" detection needs the true count, not a page-sized slice); $entries is the
    // paginated slice the table body actually renders.
    public function moodAnalysis(Request $request): void
    {
        $data = $this->moodAnalysisData($request);
        $sort = (string) $request->get('sort', 'name');
        $dir = $request->get('dir') === 'desc' ? 'desc' : 'asc';
        $sorted = $this->sortRows($data['rows'], $sort, $dir, self::MOOD_ANALYSIS_SORTABLE);
        $p = $this->paginateRows($sorted, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/mood_analysis', array_merge($data, [
            'title'      => 'Laporan Analisis Mood & Perkembangan Kondisi',
            'entries'    => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function moodAnalysisPdf(Request $request): void
    {
        $data = $this->moodAnalysisData($request);

        $table = $this->tableHtml(
            ['Name', 'Assessment Awal', 'Assessment Terakhir', 'Mood Dominan', 'Status'],
            $data['rows'],
            fn ($r) => [
                htmlspecialchars($r['name']),
                $r['first_date'] ? htmlspecialchars(date('d M Y', strtotime($r['first_date']))) . ' (PWB ' . $r['first_pwb'] . ', BDI-II ' . $r['first_bdi2'] . ')' : '-',
                $r['last_date'] ? htmlspecialchars(date('d M Y', strtotime($r['last_date']))) . ' (PWB ' . $r['last_pwb'] . ', BDI-II ' . $r['last_bdi2'] . ')' : '-',
                htmlspecialchars($r['mood_dominan']),
                htmlspecialchars($r['status']),
            ]
        );

        $this->streamPdf('Laporan Analisis Mood & Perkembangan Kondisi', 'mood_analysis', $data['filters'], $table);
    }

    private function moodAnalysisData(Request $request): array
    {
        $filters = $this->applyScope($this->commonFilters($request), 'mood-analysis');

        $sessions = $this->laporan->selfAssessmentSessions($filters);
        $byUser = [];
        foreach ($sessions as $s) {
            $byUser[$s['user_id']][] = $s;
        }

        $diaryFilters = $filters;
        unset($diaryFilters['student_ids']);
        if ($this->role() === 'counselor') {
            $diaryFilters['shared_counselor_id'] = $this->currentCounselorId();
        }
        $emotionByUser = [];
        foreach ($this->laporan->diaryRows($diaryFilters) as $d) {
            $list = json_decode($d['emotions_list'] ?? '[]', true) ?: [];
            foreach ($list as $emotion) {
                $emotionByUser[$d['user_id']][$emotion] = ($emotionByUser[$d['user_id']][$emotion] ?? 0) + 1;
            }
        }

        $rows = [];
        foreach ($byUser as $userId => $userSessions) {
            $first = $userSessions[0];
            $last = $userSessions[count($userSessions) - 1];

            $firstLevel = ($first['pwb'] && $first['bdi2']) ? $this->scoring->combinedLevel($first['pwb']['category'], $first['bdi2']['category']) : null;
            $lastLevel = ($last['pwb'] && $last['bdi2']) ? $this->scoring->combinedLevel($last['pwb']['category'], $last['bdi2']['category']) : null;

            $status = '-';
            if ($firstLevel && $lastLevel) {
                $status = $lastLevel['level'] < $firstLevel['level'] ? 'Membaik' : ($lastLevel['level'] > $firstLevel['level'] ? 'Memburuk' : 'Tetap');
            }

            $moods = $emotionByUser[$userId] ?? [];
            arsort($moods);

            $rows[] = [
                'user_id'       => $userId,
                'name'          => $first['name'],
                'student_number'           => $first['student_number'],
                'first_date' => $first['date'],
                'first_pwb'     => $first['pwb']['total_score'] ?? '-',
                'first_bdi2'    => $first['bdi2']['total_score'] ?? '-',
                'last_date'  => $last['date'],
                'last_pwb'      => $last['pwb']['total_score'] ?? '-',
                'last_bdi2'     => $last['bdi2']['total_score'] ?? '-',
                'mood_dominan'  => $moods ? array_key_first($moods) : '-',
                'status'        => $status,
                'sessions'      => $userSessions,
            ];
        }

        return ['rows' => $rows, 'filters' => $filters];
    }

    // --- 7. Evaluasi Keterlibatan Mahasiswa ---------------------------------------------

    private const ENGAGEMENT_SORTABLE = [
        'name'                       => 'name',
        'assessment_count'           => 'assessment_count',
        'diary_count'                => 'diary_count',
        'selfhelp_count'             => 'selfhelp_count',
        'booking_count'              => 'booking_count',
        'total_actions'              => 'total_actions',
    ];

    public function engagement(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        $data = $this->engagementData($request);
        $sort = (string) $request->get('sort', 'total_actions');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $rows = $this->sortRows($data['rows'], $sort, $dir, self::ENGAGEMENT_SORTABLE);
        $p = $this->paginateRows($rows, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/engagement', array_merge($data, [
            'title'      => 'Laporan Evaluasi Keterlibatan Mahasiswa',
            'rows'       => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function engagementPdf(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        $data = $this->engagementData($request);

        $table = $this->tableHtml(
            ['Name', 'Assessment', 'Diary', 'Self Help', 'Booking', 'Konseling Selesai', 'Status Keaktifan'],
            $data['rows'],
            fn ($r) => [
                htmlspecialchars($r['name']),
                $r['assessment_count'],
                $r['diary_count'],
                $r['selfhelp_count'],
                $r['booking_count'],
                $r['completed_counseling_count'],
                htmlspecialchars($r['status']),
            ]
        );

        $this->streamPdf('Laporan Evaluasi Keterlibatan Mahasiswa', 'engagement', $data['filters'], $table);
    }

    private function engagementData(Request $request): array
    {
        $filters = $this->applyScope($this->commonFilters($request), 'engagement');

        $rows = array_map(function ($r) {
            $total = (int) $r['assessment_count'] + (int) $r['diary_count'] + (int) $r['selfhelp_count'] + (int) $r['booking_count'];

            return array_merge($r, [
                'total_actions' => $total,
                'status'        => $this->engagementScoring->status($total),
            ]);
        }, $this->laporan->engagementRows($filters));

        usort($rows, fn ($a, $b) => $b['total_actions'] <=> $a['total_actions']);

        return ['rows' => $rows, 'filters' => $filters];
    }

    // --- 8. Aktivitas Konselor (Counselor Activity) --------------------------------------

    private const COUNSELOR_ACTIVITY_SORTABLE = [
        'name'            => 'name',
        'total_sessions'      => 'total_sessions',
        'total_students' => 'total_students',
    ];

    // $rows stays the full per-counselor set (the stat tiles above the table sum across
    // every counselor, not just the current page); $entries is the paginated table slice.
    public function counselorActivity(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        $data = $this->counselorActivityData($request);
        $sort = (string) $request->get('sort', 'total_sessions');
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $sorted = $this->sortRows($data['rows'], $sort, $dir, self::COUNSELOR_ACTIVITY_SORTABLE);
        $p = $this->paginateRows($sorted, max(1, (int) $request->get('page', 1)), self::REPORT_PER_PAGE);

        Response::view('laporan/counselor_activity', array_merge($data, [
            'title'      => 'Laporan Aktivitas Konselor',
            'entries'    => $p['items'],
            'total'      => $p['total'],
            'page'       => $p['page'],
            'totalPages' => $p['totalPages'],
            'sort'       => $sort,
            'dir'        => $dir,
        ]));
    }

    public function counselorActivityPdf(Request $request): void
    {
        $this->requireRole(['counselor', 'admin']);
        $data = $this->counselorActivityData($request);

        $table = $this->tableHtml(
            ['Konselor', 'Total Sesi', 'Total Mahasiswa', 'Fakultas Terbanyak', 'Kategori Risiko Terbanyak'],
            $data['rows'],
            fn ($r) => [
                htmlspecialchars($r['name']),
                $r['total_sessions'],
                $r['total_students'],
                htmlspecialchars(($r['top_faculty'] ?? '-') . ' (' . $r['top_faculty_count'] . ')'),
                htmlspecialchars(($r['top_risk'] ?? '-') . ' (' . $r['top_risk_count'] . ')'),
            ]
        );

        $this->streamPdf('Laporan Aktivitas Konselor', 'counselor_activity', $data['filters'], $table);
    }

    private function counselorActivityData(Request $request): array
    {
        $filters = $this->commonFilters($request);
        unset($filters['search']);
        if ($this->role() === 'counselor') {
            $filters['counselor_id'] = $this->currentCounselorId();
        }

        $sessions = $this->laporan->counselorActivitySessions($filters);

        $byCounselor = [];
        foreach ($sessions as $s) {
            $id = (int) $s['counselor_id'];
            $byCounselor[$id]['name'] ??= $s['counselor_name'];
            $byCounselor[$id]['specialization'] ??= $s['specialization'];
            $byCounselor[$id]['sessions'][] = $s;
            $byCounselor[$id]['student_ids'][(int) $s['user_id']] = true;
            $fak = $s['faculty'] ?: 'Tidak diketahui';
            $byCounselor[$id]['faculty_counts'][$fak] = ($byCounselor[$id]['faculty_counts'][$fak] ?? 0) + 1;
        }

        $riskLookupFilters = ['date_from' => '2000-01-01', 'date_to' => date('Y-m-d')];

        $rows = [];
        foreach ($byCounselor as $counselorId => $data) {
            $studentIds = array_keys($data['student_ids']);

            arsort($data['faculty_counts']);
            $topFaculty = array_key_first($data['faculty_counts']);

            $riskCounts = [];
            foreach ($this->laporan->latestRiskCategories(array_merge($riskLookupFilters, ['student_ids' => $studentIds])) as $rr) {
                $risk = $this->scoring->combinedLevel($rr['pwb_category'], $rr['bdi2_category']);
                $riskCounts[$risk['risk_label']] = ($riskCounts[$risk['risk_label']] ?? 0) + 1;
            }
            arsort($riskCounts);
            $topRisk = array_key_first($riskCounts);

            $rows[] = [
                'counselor_id'        => $counselorId,
                'name'               => $data['name'],
                'specialization'       => $data['specialization'],
                'total_sessions'         => count($data['sessions']),
                'total_students'    => count($studentIds),
                'top_faculty'       => $topFaculty,
                'top_faculty_count' => $topFaculty ? $data['faculty_counts'][$topFaculty] : 0,
                'top_risk'           => $topRisk,
                'top_risk_count'     => $topRisk ? $riskCounts[$topRisk] : 0,
            ];
        }

        usort($rows, fn ($a, $b) => $b['total_sessions'] <=> $a['total_sessions']);

        return ['rows' => $rows, 'filters' => $filters];
    }

    // --- Shared helpers ------------------------------------------------------------------

    private function role(): string
    {
        return $_SESSION['role'] ?? '';
    }

    private function requireRole(array $roles): void
    {
        if (!in_array($this->role(), $roles, true)) {
            http_response_code(403);
            exit('Forbidden: Anda tidak memiliki akses ke laporan ini.');
        }
    }

    private function commonFilters(Request $request): array
    {
        return [
            'date_from' => $request->get('date_from') ?: '2000-01-01',
            'date_to'   => $request->get('date_to') ?: date('Y-m-d'),
            'search'    => trim((string) $request->get('q', '')),
        ];
    }

    // Adds the role-appropriate scope key(s) to $filters for the given report slug.
    private function applyScope(array $filters, string $reportSlug): array
    {
        $role = $this->role();

        if ($role === 'student') {
            $filters['user_id'] = (int) $_SESSION['user_id'];
            return $filters;
        }

        if ($role === 'counselor') {
            if ($reportSlug === 'diary') {
                $filters['shared_counselor_id'] = $this->currentCounselorId();
            } else {
                $filters['student_ids'] = $this->laporan->counselorStudentIds($this->currentCounselorId());
            }
        }

        return $filters;
    }

    private function currentCounselorProfile(): ?array
    {
        if ($this->role() !== 'counselor') {
            return null;
        }
        if ($this->counselorProfile === null) {
            $this->counselorProfile = $this->counselors->find((int) $_SESSION['user_id']) ?: [];
        }

        return $this->counselorProfile;
    }

    private function currentCounselorId(): int
    {
        return (int) ($this->currentCounselorProfile()['counselor_id'] ?? 0);
    }

    // Every report's xxxData() does a full, unpaginated fetch (needed for accurate
    // chart/stat-tile aggregation over the whole filtered range — see e.g. diaryData()'s
    // charts or counselorActivityData()'s totals) — sortRows()/paginateRows() are applied
    // afterward, in the view action only, purely for how the table renders. PDF exports
    // always use the full unsorted/unpaginated set from xxxData() directly.
    private function sortRows(array $rows, ?string $sort, string $dir, array $sortable): array
    {
        $key = $sort !== null ? ($sortable[$sort] ?? null) : null;
        if ($key === null) {
            return $rows;
        }

        usort($rows, function ($a, $b) use ($key, $dir) {
            $av = $a[$key] ?? null;
            $bv = $b[$key] ?? null;
            $cmp = (is_numeric($av) && is_numeric($bv)) ? ($av <=> $bv) : strcmp((string) $av, (string) $bv);

            return $dir === 'asc' ? $cmp : -$cmp;
        });

        return $rows;
    }

    /** @return array{items: array, total: int, page: int, totalPages: int} */
    private function paginateRows(array $rows, int $page, int $perPage): array
    {
        $total = count($rows);
        $totalPages = (int) max(1, ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);

        return [
            'items'      => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'total'      => $total,
            'page'       => $page,
            'totalPages' => $totalPages,
        ];
    }

    /** Builds a dompdf-friendly table, with a "no data" row when $rows is empty. */
    private function tableHtml(array $headers, array $rows, callable $cells): string
    {
        $head = '<tr>' . implode('', array_map(fn ($h) => '<th>' . htmlspecialchars($h) . '</th>', $headers)) . '</tr>';

        if (!$rows) {
            $body = '<tr><td colspan="' . count($headers) . '" style="text-align:center;color:#888;">Tidak ada data pada periode ini.</td></tr>';
        } else {
            $body = '';
            foreach ($rows as $row) {
                $body .= '<tr>' . implode('', array_map(fn ($c) => '<td>' . $c . '</td>', $cells($row))) . '</tr>';
            }
        }

        return '<table class="table">' . $head . $body . '</table>';
    }

    private function periodMeta(array $filters): string
    {
        $from = htmlspecialchars(date('d M Y', strtotime($filters['date_from'])));
        $to = htmlspecialchars(date('d M Y', strtotime($filters['date_to'])));
        $extra = !empty($filters['search']) ? ' &middot; Pencarian: ' . htmlspecialchars($filters['search']) : '';

        return '<p class="subtitle">Periode: ' . $from . ' &ndash; ' . $to . $extra . '</p>';
    }

    private function streamPdf(string $title, string $slug, array $filters, string $bodyHtml): void
    {
        $html = $this->periodMeta($filters) . $bodyHtml . $this->pdf->pengesahanBlock($this->currentCounselorProfile()['name'] ?? null);
        $this->pdf->stream($title, $html, 'laporan_' . $slug . '_' . date('Ymd_His') . '.pdf');
    }
}
