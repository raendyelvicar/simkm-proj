<?php

/** @var App\Core\Router $router */

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\PasswordResetController;
use App\Controllers\DashboardController;
use App\Controllers\ProfileController;
use App\Controllers\DiaryController;
use App\Controllers\AssessmentController;
use App\Controllers\AssessmentSessionController;
use App\Controllers\AdminSettingsController;
use App\Controllers\CounselorController;
use App\Controllers\ChatController;
use App\Controllers\ConsultationController;
use App\Controllers\BookingController;
use App\Controllers\CounselorScheduleController;
use App\Controllers\BookingQueueController;
use App\Controllers\AdminScheduleController;
use App\Controllers\AdminCounselorController;
use App\Controllers\AdminApprovalController;
use App\Controllers\AdminBookingCancellationController;
use App\Controllers\ArticleController;
use App\Controllers\StudentController;
use App\Controllers\DailyTipController;
use App\Controllers\SharedDiaryController;
use App\Controllers\SelfHelpController;
use App\Controllers\ReportController;

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/register', [AuthController::class, 'showRegisterForm']);
$router->post('/register', [AuthController::class, 'register']);

$router->get('/forgot-password', [PasswordResetController::class, 'showForgotForm']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
$router->get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm']);
$router->post('/reset-password/{token}', [PasswordResetController::class, 'resetPassword']);

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

// --- Protected: counselor-only inbox for incoming student consultations ---
$router->get('/consultations', [ConsultationController::class, 'index']);
$router->get('/consultations/{studentId}', [ConsultationController::class, 'show']);
$router->post('/consultations/{studentId}', [ConsultationController::class, 'send']);
$router->get('/consultations/{studentId}/messages', [ConsultationController::class, 'messages']);

// --- Protected: student-only booking requests with a counselor ---
$router->get('/bookings', [BookingController::class, 'index']);
$router->get('/bookings/create/{counselorId}', [BookingController::class, 'create']);
$router->post('/bookings', [BookingController::class, 'store']);
$router->post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

// --- Protected: counselor-only view of their own available time slots (adding new
// slots is admin-only, see /admin/counselors/{id}/schedule below) ---
$router->get('/schedule', [CounselorScheduleController::class, 'index']);
$router->post('/schedule/{id}/toggle', [CounselorScheduleController::class, 'toggle']);

// --- Protected: counselor-only queue of pending booking requests ---
$router->get('/booking-requests', [BookingQueueController::class, 'index']);
$router->post('/booking-requests/{id}/confirm', [BookingQueueController::class, 'confirm']);
$router->post('/booking-requests/{id}/reject', [BookingQueueController::class, 'reject']);
$router->post('/booking-requests/{id}/extend', [BookingQueueController::class, 'extend']);
$router->post('/booking-requests/{id}/complete', [BookingQueueController::class, 'complete']);
$router->post('/booking-requests/{id}/no-show', [BookingQueueController::class, 'noShow']);

// --- Protected: counselor-only, read-only view of diaries students shared with them ---
$router->get('/shared-diaries', [SharedDiaryController::class, 'index']);
$router->get('/shared-diaries/{id}', [SharedDiaryController::class, 'show']);

// --- Protected: counselor-only management of daily tips shown to student ---
$router->get('/tips', [DailyTipController::class, 'index']);
$router->get('/tips/create', [DailyTipController::class, 'create']);
$router->post('/tips', [DailyTipController::class, 'store']);
$router->get('/tips/{id}/edit', [DailyTipController::class, 'edit']);
$router->post('/tips/{id}', [DailyTipController::class, 'update']);
$router->post('/tips/{id}/delete', [DailyTipController::class, 'destroy']);

// --- Protected: admin-only management of counselor accounts ---
$router->get('/admin/counselors', [AdminCounselorController::class, 'index']);
$router->get('/admin/counselors/create', [AdminCounselorController::class, 'create']);
$router->post('/admin/counselors', [AdminCounselorController::class, 'store']);
$router->get('/admin/counselors/{id}/edit', [AdminCounselorController::class, 'edit']);
$router->post('/admin/counselors/{id}', [AdminCounselorController::class, 'update']);
$router->post('/admin/counselors/{id}/status', [AdminCounselorController::class, 'toggleStatus']);

// --- Protected: admin-only — add/manage a specific counselor's bookable schedule slots ---
$router->get('/admin/counselors/{id}/schedule', [AdminScheduleController::class, 'index']);
$router->post('/admin/counselors/{id}/schedule', [AdminScheduleController::class, 'store']);
$router->post('/admin/counselors/{id}/schedule/{scheduleId}/toggle', [AdminScheduleController::class, 'toggle']);

// --- Protected: admin-only approval queue for pending student registrations ---
$router->get('/admin/approvals', [AdminApprovalController::class, 'index']);
$router->post('/admin/approvals/{id}/approve', [AdminApprovalController::class, 'approve']);
$router->post('/admin/approvals/{id}/reject', [AdminApprovalController::class, 'reject']);

// --- Protected: admin-only approval queue for student-initiated booking cancellations ---
$router->get('/admin/booking-cancellations', [AdminBookingCancellationController::class, 'index']);
$router->post('/admin/booking-cancellations/{id}/approve', [AdminBookingCancellationController::class, 'approve']);
$router->post('/admin/booking-cancellations/{id}/reject', [AdminBookingCancellationController::class, 'reject']);

$router->get('/article', [ArticleController::class, 'index']);
$router->get('/article/create', [ArticleController::class, 'create']);
$router->post('/article', [ArticleController::class, 'store']);
$router->get('/article/{id}', [ArticleController::class, 'show']);
$router->get('/article/{id}/edit', [ArticleController::class, 'edit']);
$router->post('/article/{id}', [ArticleController::class, 'update']);
$router->post('/article/{id}/delete', [ArticleController::class, 'destroy']);

$router->get('/major', [App\Controllers\LookupController::class, 'getMajor']);

// --- Protected: student self-help features (breathing, gratitude, activities, PFA) ---
$router->get('/self-help', [SelfHelpController::class, 'index']);
$router->get('/self-help/breathing', [SelfHelpController::class, 'breathing']);
$router->get('/self-help/gratitude', [SelfHelpController::class, 'gratitude']);
$router->get('/self-help/pfa', [SelfHelpController::class, 'pfa']);
$router->get('/self-help/activities', [SelfHelpController::class, 'activities']);
$router->get('/self-help/activities/create', [SelfHelpController::class, 'createActivity']);
$router->post('/self-help/activities', [SelfHelpController::class, 'storeActivity']);
$router->post('/self-help/activities/{id}/complete', [SelfHelpController::class, 'completeActivity']);
$router->post('/self-help/activities/{id}/skip', [SelfHelpController::class, 'skipActivity']);
$router->post('/self-help/activities/{id}/delete', [SelfHelpController::class, 'destroyActivity']);

// --- Protected: Laporan hub + the 8 report pages. Each report enforces its own
// role/scope rule inside ReportController regardless of what the nav shows. ---
$router->get('/laporan', [ReportController::class, 'index']);

$router->get('/laporan/self-assessment', [ReportController::class, 'selfAssessment']);
$router->get('/laporan/self-assessment/pdf', [ReportController::class, 'selfAssessmentPdf']);

$router->get('/laporan/diary', [ReportController::class, 'diary']);
$router->get('/laporan/diary/pdf', [ReportController::class, 'diaryPdf']);

$router->get('/laporan/self-help', [ReportController::class, 'selfHelp']);
$router->get('/laporan/self-help/pdf', [ReportController::class, 'selfHelpPdf']);

$router->get('/laporan/konseling', [ReportController::class, 'konseling']);
$router->get('/laporan/konseling/pdf', [ReportController::class, 'konselingPdf']);

$router->get('/laporan/risk-mapping', [ReportController::class, 'riskMapping']);
$router->get('/laporan/risk-mapping/pdf', [ReportController::class, 'riskMappingPdf']);

$router->get('/laporan/mood-analysis', [ReportController::class, 'moodAnalysis']);
$router->get('/laporan/mood-analysis/pdf', [ReportController::class, 'moodAnalysisPdf']);

$router->get('/laporan/engagement', [ReportController::class, 'engagement']);
$router->get('/laporan/engagement/pdf', [ReportController::class, 'engagementPdf']);

$router->get('/laporan/counselor-activity', [ReportController::class, 'counselorActivity']);
$router->get('/laporan/counselor-activity/pdf', [ReportController::class, 'counselorActivityPdf']);
