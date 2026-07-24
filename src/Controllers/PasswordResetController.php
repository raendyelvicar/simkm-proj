<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;

class PasswordResetController
{
    private const TOKEN_TTL_MINUTES = 60;

    private UserRepository $users;
    private PasswordResetRepository $resets;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->resets = new PasswordResetRepository();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // GET /forgot-password
    public function showForgotForm(Request $request): void
    {
        $successReset = $_SESSION['successReset'] ?? null;
        unset($_SESSION['successReset']);

        Response::view('auth/forgot_password', [
            'title' => 'Lupa Password',
            'success' => $successReset,
        ]);
    }

    // POST /forgot-password
    //
    // Always shows the same generic confirmation regardless of whether the email is
    // registered — telling the caller "email not found" lets an attacker enumerate
    // registered accounts, so the response (and timing) must look identical either way.
    public function sendResetLink(Request $request): void
    {
        $email = trim($request->post('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::view('auth/forgot_password', [
                'title' => 'Lupa Password',
                'error' => 'Masukkan alamat email yang valid.',
                'old' => ['email' => $email],
            ]);
            return;
        }

        $user = $this->users->findByEmail($email);
        if ($user) {
            $this->issueAndSendResetLink($user->id, $user->email, $user->name ?: $user->username);
        }

        $_SESSION['successReset'] = 'Jika email tersebut terdaftar, kami telah mengirimkan tautan reset password. Silakan periksa kotak masuk (dan folder spam) email Anda.';
        Response::redirect('/forgot-password');
    }

    private function issueAndSendResetLink(int $userId, string $email, string $name): void
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_MINUTES * 60);

        $this->resets->create($userId, $tokenHash, $expiresAt);

        require_once __DIR__ . '/../../config/send_email.php';

        $resetUrl = rtrim(env('APP_URL', ''), '/') . '/reset-password/' . $token;
        $subject = 'Reset Password - SIMKM';
        $message = "Halo {$name},\n\n"
            . "Kami menerima permintaan untuk mereset password akun SIMKM Anda. "
            . "Klik tautan di bawah ini untuk membuat password baru:\n\n"
            . "{$resetUrl}\n\n"
            . 'Tautan ini hanya berlaku selama ' . self::TOKEN_TTL_MINUTES . " menit.\n\n"
            . 'Jika Anda tidak merasa meminta reset password, abaikan email ini — password Anda tidak akan berubah.';

        try {
            kirimEmail($email, $subject, $message);
        } catch (\Throwable $e) {
            error_log('PasswordResetController::issueAndSendResetLink failed for user ' . $userId . ': ' . $e->getMessage());
        }
    }

    // GET /reset-password/{token}
    public function showResetForm(Request $request, string $token): void
    {
        $tokenRow = $this->resets->findValidByTokenHash(hash('sha256', $token));

        if (!$tokenRow) {
            Response::view('auth/reset_password', [
                'title' => 'Reset Password',
                'invalid' => true,
            ]);
            return;
        }

        Response::view('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    // POST /reset-password/{token}
    public function resetPassword(Request $request, string $token): void
    {
        $tokenRow = $this->resets->findValidByTokenHash(hash('sha256', $token));

        if (!$tokenRow) {
            Response::view('auth/reset_password', [
                'title' => 'Reset Password',
                'invalid' => true,
            ]);
            return;
        }

        $password = $request->post('password', '');
        $passwordConfirmation = $request->post('password_confirmation', '');

        $error = null;
        if (strlen($password) < 8) {
            $error = 'Password minimal 8 karakter.';
        } elseif ($password !== $passwordConfirmation) {
            $error = 'Konfirmasi password tidak cocok.';
        }

        if ($error) {
            Response::view('auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'error' => $error,
            ]);
            return;
        }

        $this->users->updatePassword((int) $tokenRow['user_id'], password_hash($password, PASSWORD_DEFAULT));
        $this->resets->markUsed((int) $tokenRow['id']);

        $_SESSION['successRegister'] = 'Password berhasil diperbarui. Silakan masuk dengan password baru Anda.';
        Response::redirect('/login');
    }
}
