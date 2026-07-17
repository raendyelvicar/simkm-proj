<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\SettingsRepository;

// Admin-only screen for system settings — currently just the combined
// BDI-II+PWB session time limit used by AssessmentSessionController.
class AdminSettingsController
{
    private SettingsRepository $settings;

    public function __construct()
    {
        AuthMiddleware::handle();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Forbidden: admin only.');
        }

        $this->settings = new SettingsRepository();
    }

    // GET /admin/settings
    public function index(Request $request): void
    {
        Response::view('admin/settings/index', [
            'title'            => 'Pengaturan Sistem',
            'timeLimitMinutes' => (int) $this->settings->get('assessment_time_limit_minutes', '45'),
        ]);
    }

    // POST /admin/settings
    public function update(Request $request): void
    {
        $minutes = (int) $request->post('assessment_time_limit_minutes', 0);

        if ($minutes < 1 || $minutes > 240) {
            $_SESSION['error'] = 'Batas waktu harus antara 1 dan 240 menit.';
            Response::redirect('/admin/settings');
            return;
        }

        $this->settings->set('assessment_time_limit_minutes', (string) $minutes);
        $_SESSION['success'] = 'Pengaturan berhasil disimpan.';
        Response::redirect('/admin/settings');
    }
}
