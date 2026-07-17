<?php

/** @var App\Core\Router $router */

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\DiaryController;
use App\Controllers\AssessmentController;
use App\Controllers\AssessmentSessionController;
use App\Controllers\AdminSettingsController;
use App\Controllers\CounselorController;
use App\Controllers\ChatController;
use App\Controllers\ConsultationController;
use App\Controllers\AdminCounselorController;
use App\Controllers\AdminApprovalController;
use App\Controllers\ArticleController;
use App\Controllers\StudentController;
use App\Controllers\ReportController;
use App\Controllers\DailyTipController;
use App\Controllers\SharedDiaryController;

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/register', [AuthController::class, 'showRegisterForm']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/demo', [UserController::class, 'demo']);

// --- Protected: requires login (each controller enforces via AuthMiddleware) ---
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/profile', [ProfileController::class, 'show']);
$router->post('/profile', [ProfileController::class, 'update']);

$router->get('/diary', [DiaryController::class, 'index']);
$router->get('/diary/create', [DiaryController::class, 'create']);
$router->post('/diary', [DiaryController::class, 'store']);
$router->get('/diary/{id}', [DiaryController::class, 'show']);
$router->get('/diary/{id}/edit', [DiaryController::class, 'edit']);
$router->post('/diary/{id}', [DiaryController::class, 'update']);
$router->post('/diary/{id}/delete', [DiaryController::class, 'destroy']);

$router->get('/assessment', [AssessmentController::class, 'index']);
$router->get('/assessment/history', [AssessmentController::class, 'history']);
$router->get('/assessment/history/student/{id}', [AssessmentController::class, 'studentHistory']);
$router->get('/assessment/history/{id}/pdf', [AssessmentController::class, 'exportPdf']);
$router->get('/assessment/result/{id}', [AssessmentController::class, 'result']);

// --- Combined, timed BDI-II+PWB fill-in flow (one continuous session, AJAX) ---
$router->get('/assessment/start', [AssessmentSessionController::class, 'start']);
$router->post('/assessment/session', [AssessmentSessionController::class, 'create']);
$router->get('/assessment/session', [AssessmentSessionController::class, 'show']);
$router->get('/assessment/session/state', [AssessmentSessionController::class, 'state']);
$router->post('/assessment/session/answer', [AssessmentSessionController::class, 'answer']);
$router->post('/assessment/session/finish', [AssessmentSessionController::class, 'finish']);
$router->get('/assessment/session/complete/{id}', [AssessmentSessionController::class, 'complete']);

// --- Protected: admin-only system settings (e.g. assessment session time limit) ---
$router->get('/admin/settings', [AdminSettingsController::class, 'index']);
$router->post('/admin/settings', [AdminSettingsController::class, 'update']);

$router->get('/students', [StudentController::class, 'index']);

// --- Public ---
$router->get('/counselor', [CounselorController::class, 'index']);
$router->get('/counselor/{id}', [CounselorController::class, 'show']);

// --- Protected: requires login (consultation chat with a counselor) ---
$router->get('/chat/{counselorId}', [ChatController::class, 'show']);
$router->post('/chat/{counselorId}', [ChatController::class, 'send']);
$router->get('/chat/{counselorId}/messages', [ChatController::class, 'messages']);

// --- Protected: konselor-only inbox for incoming student consultations ---
$router->get('/consultations', [ConsultationController::class, 'index']);
$router->get('/consultations/{studentId}', [ConsultationController::class, 'show']);
$router->post('/consultations/{studentId}', [ConsultationController::class, 'send']);
$router->get('/consultations/{studentId}/messages', [ConsultationController::class, 'messages']);

// --- Protected: konselor-only, read-only view of diaries students shared with them ---
$router->get('/shared-diaries', [SharedDiaryController::class, 'index']);
$router->get('/shared-diaries/{id}', [SharedDiaryController::class, 'show']);

// --- Protected: konselor-only management of daily tips shown to mahasiswa ---
$router->get('/tips', [DailyTipController::class, 'index']);
$router->get('/tips/create', [DailyTipController::class, 'create']);
$router->post('/tips', [DailyTipController::class, 'store']);
$router->get('/tips/{id}/edit', [DailyTipController::class, 'edit']);
$router->post('/tips/{id}', [DailyTipController::class, 'update']);
$router->post('/tips/{id}/delete', [DailyTipController::class, 'destroy']);

// --- Protected: admin-only management of konselor accounts ---
$router->get('/admin/counselors', [AdminCounselorController::class, 'index']);
$router->get('/admin/counselors/create', [AdminCounselorController::class, 'create']);
$router->post('/admin/counselors', [AdminCounselorController::class, 'store']);
$router->get('/admin/counselors/{id}/edit', [AdminCounselorController::class, 'edit']);
$router->post('/admin/counselors/{id}', [AdminCounselorController::class, 'update']);
$router->post('/admin/counselors/{id}/status', [AdminCounselorController::class, 'toggleStatus']);

// --- Protected: admin-only approval queue for pending mahasiswa registrations ---
$router->get('/admin/approvals', [AdminApprovalController::class, 'index']);
$router->post('/admin/approvals/{id}/approve', [AdminApprovalController::class, 'approve']);
$router->post('/admin/approvals/{id}/reject', [AdminApprovalController::class, 'reject']);

$router->get('/article', [ArticleController::class, 'index']);
$router->get('/article/create', [ArticleController::class, 'create']);
$router->post('/article', [ArticleController::class, 'store']);
$router->get('/article/{id}', [ArticleController::class, 'show']);
$router->get('/article/{id}/edit', [ArticleController::class, 'edit']);
$router->post('/article/{id}', [ArticleController::class, 'update']);
$router->post('/article/{id}/delete', [ArticleController::class, 'destroy']);

$router->get('/jurusan', [App\Controllers\LookupController::class, 'getJurusan']);


$router->get('/report/user', [ReportController::class, 'user']);
$router->get('/report/diary', [ReportController::class, 'diary']);
$router->get('/report/assessment', [ReportController::class, 'assessment']);
$router->get('/report/consultation', [ReportController::class, 'consultation']);
$router->get('/report/stress', [ReportController::class, 'stress']);
$router->get('/report/activity', [ReportController::class, 'activity']);
$router->get('/report/statistic-mood', [ReportController::class, 'statistikMood']);
$router->get('/report/export', [ReportController::class, 'export']);
