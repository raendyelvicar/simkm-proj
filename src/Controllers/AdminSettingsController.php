<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\SettingsRepository;

// Admin-only screen for system settings — the combined BDI-II+PWB session time limit
// used by AssessmentSessionController, and the default "Mengetahui" counselor used on
// report PDF exports (ReportController::defaultCounselorName()) when no more specific
// counselor applies.
class AdminSettingsController
{
    private SettingsRepository $settings;
    private CounselorRepository $counselors;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->settings = new SettingsRepository();
        $this->counselors = new CounselorRepository();
    }

    // GET /admin/settings
    public function index(Request $request): void
    {
        // Only counselors with a completed profile have a counselor_id to store/reference
        // (bare accounts hydrate counselor_id as 0 — see Counselor::__construct()).
        $counselors = array_values(array_filter(
            $this->counselors->all(false),
            fn ($c) => $c['counselor_id'] > 0
        ));

        Response::view('admin/settings/index', [
            'title'                     => 'Pengaturan Sistem',
            'timeLimitMinutes'          => (int) $this->settings->get('assessment_time_limit_minutes', '45'),
            'counselors'                => $counselors,
            'defaultReportCounselorId'  => (int) ($this->settings->get('report_default_counselor_id') ?? 0),
        ]);
    }

    // POST /admin/settings
    public function update(Request $request): void
    {
        $minutes = (int) $request->post('assessment_time_limit_minutes', 0);
        $reportCounselorId = (int) $request->post('report_default_counselor_id', 0);

        if ($minutes < 1 || $minutes > 240) {
            $_SESSION['error'] = 'Batas waktu harus antara 1 dan 240 menit.';
            Response::redirect('/admin/settings');
            return;
        }

        if ($reportCounselorId && !$this->counselors->findByCounselorId($reportCounselorId)) {
            $_SESSION['error'] = 'Konselor default laporan tidak valid.';
            Response::redirect('/admin/settings');
            return;
        }

        $this->settings->set('assessment_time_limit_minutes', (string) $minutes);
        $this->settings->set('report_default_counselor_id', (string) $reportCounselorId);
        $_SESSION['success'] = 'Pengaturan berhasil disimpan.';
        Response::redirect('/admin/settings');
    }
}
