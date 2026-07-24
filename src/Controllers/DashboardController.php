<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\AssessmentRepository;
use App\Repositories\DailyTipRepository;

class DashboardController
{
    private AssessmentRepository $assessments;
    private DailyTipRepository $tips;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->assessments = new AssessmentRepository();
        $this->tips = new DailyTipRepository();
    }

    // GET /dashboard
    public function index(Request $request): void
    {
        $role = $_SESSION['role'] ?? '';
        $isStaff = in_array($role, ['admin', 'counselor'], true);

        $data = [
            'title'    => 'Dashboard',
            'username' => $_SESSION['username'] ?? '',
            'role'     => $role,
        ];

        if ($isStaff) {
            $data['assessCountsBdi2'] = $this->assessments->countsByCategory('bdi2');
            $data['assessCountsPwb']  = $this->assessments->countsByCategory('pwb');
        } else {
            $userId = (int) $_SESSION['user_id'];
            $data['assessLatestBdi2'] = $this->assessments->latestForUser($userId, 'bdi2')?->toArray();
            $data['assessLatestPwb']  = $this->assessments->latestForUser($userId, 'pwb')?->toArray();

            if (!empty($_SESSION['show_daily_tip'])) {
                unset($_SESSION['show_daily_tip']);
                $data['dailyTip'] = $this->tips->randomActive()?->toArray();
            }
        }

        Response::view('dashboard/index', $data);
    }
}
