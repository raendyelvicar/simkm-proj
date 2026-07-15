<?php

/**
 * includes/auth_functions.php
 *
 * Reusable authentication helpers used by login.php (and later by
 * register.php / password-reset flows if needed).
 */

/**
 * Validate raw login input.
 * Returns an error message string, or null if input is valid.
 */
function validateLoginInput(string $username, string $password): ?string
{
    if ($username === '' || $password === '') {
        return "Username dan password wajib diisi.";
    }

    return null;
}

/**
 * Look up a user by username. Returns an associative array or null if not found.
 */
function findUserByUsername(mysqli $mysqli, string $username): ?array
{
    $stmt = $mysqli->prepare(
        "SELECT id, username, password, role, status FROM users WHERE username = ? LIMIT 1"
    );

    if (!$stmt) {
        throw new RuntimeException("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $db_username, $db_password, $role, $status);

    if ($stmt->num_rows === 0 || !$stmt->fetch()) {
        $stmt->close();
        return null;
    }

    $stmt->close();

    return [
        'id'       => $id,
        'username' => $db_username,
        'password' => $db_password,
        'role'     => $role,
        'status'   => $status,
    ];
}

/**
 * Verify a submitted password against the stored hash.
 * No plaintext fallback — every stored password must be a proper hash.
 */
function verifyPassword(string $submittedPassword, ?string $storedHash): bool
{
    return !empty($storedHash) && password_verify($submittedPassword, $storedHash);
}

/**
 * Check account status. Returns an error message if login should be blocked,
 * or null if the account is active and allowed to log in.
 */
function checkAccountStatus(string $status): ?string
{
    return match ($status) {
        'pending'  => "Akun Anda masih menunggu persetujuan Administrator.",
        'rejected' => "Pendaftaran Anda ditolak Administrator.",
        default    => null,
    };
}

/**
 * Establish an authenticated session for the given user.
 * Regenerates the session ID first to prevent session fixation.
 */
function startUserSession(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
}

/**
 * Record a login event in the log_login table.
 */
function logLogin(mysqli $mysqli, int $userId, string $ipAddress): void
{
    $stmt = $mysqli->prepare("INSERT INTO log_login (user_id, ip_address) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

/**
 * Send a login notification email for select roles.
 * Silently no-ops if send_email.php or kirimEmail() aren't available,
 * or if the notify-to address isn't configured.
 */
function notifyLoginByEmail(array $user): void
{
    $notifiableRoles = ['mahasiswa', 'konselor'];

    if (!in_array($user['role'], $notifiableRoles, true)) {
        return;
    }

    $notifyTo = $_ENV['ADMIN_NOTIFY_EMAIL'] ?? getenv('ADMIN_NOTIFY_EMAIL');
    if (!$notifyTo) {
        return;
    }

    if (!file_exists(__DIR__ . '/../config/send_email.php')) {
        return;
    }

    require_once __DIR__ . '/../config/send_email.php';

    if (!function_exists('kirimEmail')) {
        return;
    }

    try {
        kirimEmail(
            $notifyTo,
            "Login " . ucfirst($user['role']),
            "User login:\nUsername: {$user['username']}\nRole: {$user['role']}\nWaktu: " . date('Y-m-d H:i:s')
        );
    } catch (Exception $e) {
        error_log('notifyLoginByEmail failed: ' . $e->getMessage());
    }
}

/**
 * Return the dashboard URL a user should be redirected to after login.
 */
function dashboardUrlForRole(string $role): string
{
    $knownRoles = ['admin', 'mahasiswa', 'konselor'];

    return in_array($role, $knownRoles, true)
        ? 'dashboard_bootstrap/dashboard_bootstrap.php'
        : 'redirect_dashboard.php';
}
