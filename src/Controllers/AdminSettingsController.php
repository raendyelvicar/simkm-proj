<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\CounselorRepository;
use App\Repositories\SettingsRepository;

// Admin-only screen for system settings — the combined BDI-II+PWB session time limit
// used by AssessmentSessionController, the default "Mengetahui" counselor used on
// report PDF exports (ReportController::defaultCounselorName()) when no more specific
// counselor applies, and the organization letterhead (name/address/phone/email/logo)
// shown on every Laporan report's PDF export (see ReportPdfService::orgHeaderHtml()).
class AdminSettingsController
{
    private const ALLOWED_LOGO_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    private const MAX_LOGO_BYTES = 2 * 1024 * 1024;

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
            'orgName'                   => $this->settings->get('org_name', ''),
            'orgAddress'                => $this->settings->get('org_address', ''),
            'orgPhone'                  => $this->settings->get('org_phone', ''),
            'orgEmail'                  => $this->settings->get('org_email', ''),
            'orgLogoPath'               => $this->settings->get('org_logo_path', ''),
        ]);
    }

    // POST /admin/settings
    public function update(Request $request): void
    {
        $minutes = (int) $request->post('assessment_time_limit_minutes', 0);
        $reportCounselorId = (int) $request->post('report_default_counselor_id', 0);
        $orgName = trim($request->post('org_name', ''));
        $orgAddress = trim($request->post('org_address', ''));
        $orgPhone = trim($request->post('org_phone', ''));
        $orgEmail = trim($request->post('org_email', ''));
        $removeLogo = (bool) $request->post('remove_org_logo', false);

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

        [$logoPath, $logoError] = $this->handleLogoUpload($request);
        if ($logoError) {
            $_SESSION['error'] = $logoError;
            Response::redirect('/admin/settings');
            return;
        }

        $this->settings->set('assessment_time_limit_minutes', (string) $minutes);
        $this->settings->set('report_default_counselor_id', (string) $reportCounselorId);
        $this->settings->set('org_name', $orgName);
        $this->settings->set('org_address', $orgAddress);
        $this->settings->set('org_phone', $orgPhone);
        $this->settings->set('org_email', $orgEmail);

        if ($logoPath) {
            $this->settings->set('org_logo_path', $logoPath);
        } elseif ($removeLogo) {
            $this->settings->set('org_logo_path', '');
        }

        $_SESSION['success'] = 'Pengaturan berhasil disimpan.';
        Response::redirect('/admin/settings');
    }

    // Returns [publicPath|null, error|null]. Leaves the existing logo untouched when no
    // file is chosen — mirrors AdminCounselorController::handleImageUpload().
    private function handleLogoUpload(Request $request): array
    {
        $file = $request->file('org_logo');

        if (!$file) {
            return [null, null];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Gagal mengunggah logo.'];
        }

        if ($file['size'] > self::MAX_LOGO_BYTES) {
            return [null, 'Ukuran logo maksimal 2MB.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_LOGO_TYPES[$ext]) || self::ALLOWED_LOGO_TYPES[$ext] !== $mime) {
            return [null, 'Logo harus berformat JPG, PNG, atau WEBP.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return [null, 'Gagal mengunggah logo.'];
        }

        $dir = __DIR__ . '/../../public/uploads/org';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [null, 'Gagal mengunggah logo.'];
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return [null, 'Gagal mengunggah logo.'];
        }

        return ['/uploads/org/' . $filename, null];
    }
}
