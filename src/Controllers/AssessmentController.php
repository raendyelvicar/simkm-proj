<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Models\AssessmentSubmission;
use App\Repositories\AssessmentRepository;
use App\Repositories\UserRepository;
use App\Services\AssessmentScoringService;
use App\Support\AssessmentMeta;
use Dompdf\Dompdf;
use Dompdf\Options;

class AssessmentController
{
    private const TYPES = AssessmentMeta::TYPES;
    private const META = AssessmentMeta::META;
    private const PER_PAGE = 10;

    private AssessmentRepository $assessments;
    private AssessmentScoringService $scoring;
    private UserRepository $users;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->assessments = new AssessmentRepository();
        $this->scoring = new AssessmentScoringService();
        $this->users = new UserRepository();
    }

    // GET /assessment
    public function index(Request $request): void
    {
        $userId = (int) $_SESSION['user_id'];

        if ($this->isStaff()) {
            Response::view('assessment/index', [
                'title'                 => 'Self-Assessment',
                'isStaff'               => true,
                'meta'                  => self::META,
                'countsBdi2'            => $this->assessments->countsByCategory('bdi2'),
                'countsPwb'             => $this->assessments->countsByCategory('pwb'),
                'recentFlagged'         => array_map(
                    fn ($s) => $s->toArray(),
                    $this->assessments->recentByCategories(['Berat', 'Rendah'], 5)
                ),
                'fakultasCounts'        => $this->users->countByFakultas(),
                'pwbDimensionAverages'  => $this->assessments->pwbDimensionAverages(),
                'bdi2ItemAverages'      => $this->assessments->bdi2ItemAverages(),
                'suicidalIdeationFlags' => $this->assessments->flaggedForSuicidalIdeation(10),
                'participation'         => $this->assessments->participationStats(),
                'activeMahasiswaCount'  => $this->users->countActiveMahasiswa(),
            ]);
            return;
        }

        Response::view('assessment/index', [
            'title'      => 'Self-Assessment',
            'isStaff'    => false,
            'meta'       => self::META,
            'latestBdi2' => $this->assessments->latestForUser($userId, 'bdi2')?->toArray(),
            'latestPwb'  => $this->assessments->latestForUser($userId, 'pwb')?->toArray(),
        ]);
    }

    // GET /assessment/result/{id}
    public function result(Request $request, string $id): void
    {
        $submission = $this->findViewableSubmission((int) $id);
        if (!$submission) {
            return;
        }

        $type = $submission->type;
        $feedback = $type === 'bdi2'
            ? $this->scoring->bdi2Feedback($submission->category)
            : $this->scoring->pwbOverallFeedback($submission->category);
        $tips = $type === 'bdi2' ? $this->scoring->bdi2Tips($submission->category) : [];

        $otherType = $type === 'bdi2' ? 'pwb' : 'bdi2';
        $otherSubmission = $this->assessments->latestForUser($submission->userId, $otherType);

        $combined = null;
        if ($otherSubmission) {
            $pwbCategory = $type === 'pwb' ? $submission->category : $otherSubmission->category;
            $bdi2Category = $type === 'bdi2' ? $submission->category : $otherSubmission->category;
            $combined = $this->scoring->combinedLevel($pwbCategory, $bdi2Category);
            $combined['other_type_label'] = self::META[$otherType]['short_title'];
            $combined['other_submitted_at'] = $otherSubmission->submittedAt;
        }

        Response::view('assessment/result', [
            'title'      => 'Hasil ' . self::META[$type]['short_title'],
            'meta'       => self::META[$type],
            'submission' => $submission->toArray(),
            'answers'    => $this->assessments->answersForSubmission($submission->id),
            'feedback'   => $feedback,
            'tips'       => $tips,
            'isStaff'    => $this->isStaff(),
            'combined'   => $combined,
        ]);
    }

    // GET /assessment/history
    public function history(Request $request): void
    {
        if ($this->isStaff()) {
            $this->staffHistory($request);
            return;
        }

        $type = $request->get('type');
        $type = in_array($type, self::TYPES, true) ? $type : null;
        $submissions = $this->assessments->submissionsForUser((int) $_SESSION['user_id'], $type);

        Response::view('assessment/history', [
            'title'       => 'Riwayat Assessment',
            'isStaff'     => false,
            'type'        => $type,
            'meta'        => self::META,
            'submissions' => array_map(fn ($s) => $s->toArray(), $submissions),
        ]);
    }

    // GET /assessment/history (staff branch) — students grouped one row each, with search/filter/pagination.
    private function staffHistory(Request $request): void
    {
        $filters = [
            'search'        => trim((string) $request->get('q', '')),
            'fakultas'      => $request->get('fakultas') ?: null,
            'jurusan'       => $request->get('jurusan') ?: null,
            'bdi2_category' => $request->get('bdi2_category') ?: null,
            'pwb_category'  => $request->get('pwb_category') ?: null,
        ];
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->assessments->studentAssessmentSummaries($filters, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('assessment/history', [
            'title'           => 'Riwayat Assessment',
            'isStaff'         => true,
            'meta'            => self::META,
            'students'        => $result['items'],
            'total'           => $result['total'],
            'page'            => $page,
            'totalPages'      => $totalPages,
            'filters'         => $filters,
            'fakultasOptions' => array_keys($this->users->countByFakultas()),
            'jurusanOptions'  => $this->users->distinctJurusan(),
        ]);
    }

    // GET /assessment/history/student/{id} — staff-only: one student's full assessment history.
    public function studentHistory(Request $request, string $id): void
    {
        if (!$this->isStaff()) {
            http_response_code(403);
            exit('Forbidden: hanya admin/konselor yang dapat melihat halaman ini.');
        }

        $student = $this->users->find((int) $id);
        if (!$student || $student->role !== 'mahasiswa') {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Mahasiswa Tidak Ditemukan']);
            return;
        }

        $filters = [
            'type'     => in_array($request->get('type'), self::TYPES, true) ? $request->get('type') : null,
            'category' => $request->get('category') ?: null,
        ];
        $page = max(1, (int) $request->get('page', 1));

        $result = $this->assessments->submissionsForUserFiltered((int) $id, $filters, $page, self::PER_PAGE);
        $totalPages = (int) max(1, ceil($result['total'] / self::PER_PAGE));

        Response::view('assessment/history_student', [
            'title'       => 'Riwayat Assessment — ' . $student->nama,
            'meta'        => self::META,
            'student'     => $student->toArray(),
            'submissions' => array_map(fn ($s) => $s->toArray(), $result['items']),
            'total'       => $result['total'],
            'page'        => $page,
            'totalPages'  => $totalPages,
            'filters'     => $filters,
        ]);
    }

    // GET /assessment/history/{id}/pdf
    public function exportPdf(Request $request, string $id): void
    {
        $submission = $this->findViewableSubmission((int) $id);
        if (!$submission) {
            return;
        }

        $answers = $this->assessments->answersForSubmission($submission->id);
        $meta = self::META[$submission->type];

        $html = $this->renderPdfHtml($submission, $answers, $meta);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('assessment_' . $submission->type . '_' . $submission->id . '.pdf', ['Attachment' => true]);
        exit;
    }

    private function renderPdfHtml(AssessmentSubmission $submission, array $answers, array $meta): string
    {
        $rows = '';
        foreach ($answers as $answer) {
            $rows .= '<tr><td>' . (int) $answer['order_no'] . '. ' . htmlspecialchars($answer['question_text'])
                . '</td><td>' . htmlspecialchars($answer['label']) . '</td><td style="text-align:center">' . (int) $answer['score_value'] . '</td></tr>';
        }

        $dimensionRows = '';
        foreach ($submission->dimensionScores as $dim) {
            $dimensionRows .= '<tr><td>' . htmlspecialchars($dim['label']) . '</td><td style="text-align:center">'
                . $dim['score'] . ' / ' . $dim['max_score'] . '</td><td style="text-align:center">' . htmlspecialchars($dim['category']) . '</td></tr>';
        }

        $percentageRow = $submission->categoryPercentage !== null
            ? '<tr><td class="label">Persentase</td><td>' . $submission->categoryPercentage . '%</td></tr>'
            : '';

        $dimensionSection = $dimensionRows !== ''
            ? '<h2>Skor per Dimensi</h2><table class="table"><tr><th>Dimensi</th><th>Skor</th><th>Kategori</th></tr>' . $dimensionRows . '</table>'
            : '';

        return '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body{ font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111; }
h1{ text-align:center; color:#2563eb; margin-bottom:8px; }
h2{ color:#2563eb; font-size:14px; margin-top:24px; }
.table{ width:100%; border-collapse:collapse; margin-top:8px; }
.table td, .table th{ padding:8px; border:1px solid #ddd; font-size:11px; }
.label{ width:30%; font-weight:bold; background:#f5f5f5; }
.note{ margin-top:24px; padding:12px; background:#f9f9f9; border-left:4px solid #2563eb; font-size:11px; }
.footer{ margin-top:32px; text-align:right; font-size:10px; color:#777; }
</style>
</head>
<body>
<h1>Hasil ' . htmlspecialchars($meta['short_title']) . '</h1>
<p style="text-align:center;color:#555;">' . htmlspecialchars($meta['title']) . '</p>
<table class="table">
<tr><td class="label">Nama</td><td>' . htmlspecialchars($submission->userName ?? '-') . '</td></tr>
<tr><td class="label">Tanggal Assessment</td><td>' . htmlspecialchars(date('d F Y H:i', strtotime($submission->submittedAt))) . '</td></tr>
<tr><td class="label">Total Skor</td><td>' . $submission->totalScore . ' / ' . $submission->maxScore . '</td></tr>
<tr><td class="label">Kategori</td><td>' . htmlspecialchars($submission->category) . '</td></tr>
' . $percentageRow . '
</table>
' . $dimensionSection . '
<h2>Rincian Jawaban</h2>
<table class="table"><tr><th>Pertanyaan</th><th>Jawaban</th><th>Skor</th></tr>' . $rows . '</table>
<div class="note"><b>Catatan:</b><br>Dokumen ini bersifat pribadi dan rahasia. Hasil assessment ini bukan merupakan diagnosis medis. Apabila kondisi ini dirasakan mengganggu aktivitas sehari-hari, disarankan untuk berkonsultasi dengan konselor atau tenaga profesional.</div>
<div class="footer">Dicetak otomatis oleh SIMKM</div>
</body>
</html>';
    }

    private function findViewableSubmission(int $id): ?AssessmentSubmission
    {
        $submission = $this->assessments->findSubmission($id);

        if (!$submission) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Hasil Tidak Ditemukan']);
            return null;
        }

        if (!$this->isStaff() && $submission->userId !== (int) $_SESSION['user_id']) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Hasil Tidak Ditemukan']);
            return null;
        }

        return $submission;
    }

    private function isStaff(): bool
    {
        return in_array($_SESSION['role'] ?? '', ['admin', 'konselor'], true);
    }
}
