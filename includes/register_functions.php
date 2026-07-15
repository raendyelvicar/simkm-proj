<?php

/**
 * includes/register_functions.php
 *
 * Reusable helpers used by public/register.php.
 * Uses PDO (via App\Core\Database::connection()) to match login.php.
 */

/**
 * Validate required fields + password length.
 * Returns an array of error strings (empty array = valid).
 */
function validateRegistrationInput(array $data): array
{
    $errors = [];

    $required = ['nama', 'npm', 'username', 'email', 'password', 'jenis_kelamin', 'fakultas', 'jurusan', 'no_hp'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Semua field wajib diisi.";
            break; // one generic message is enough, matches original behavior
        }
    }

    if (empty($errors) && strlen($data['password']) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    return $errors;
}

/**
 * Run a single "does this value already exist" check against a given column.
 * Column name is validated against an allowlist since it's interpolated
 * into the SQL string (PDO params can't bind identifiers, only values).
 */
function valueExistsInUsers(PDO $pdo, string $column, string $value): bool
{
    $allowedColumns = ['username', 'email', 'npm'];
    if (!in_array($column, $allowedColumns, true)) {
        throw new InvalidArgumentException("Unsupported column: {$column}");
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE {$column} = :value LIMIT 1");
    $stmt->execute(['value' => $value]);

    return (bool) $stmt->fetch();
}

/**
 * Check username/email/npm uniqueness together.
 * Returns an array of specific error messages (empty array = all unique).
 */
function checkUniqueRegistrationFields(PDO $pdo, string $username, string $email, string $npm): array
{
    $errors = [];

    if (valueExistsInUsers($pdo, 'username', $username)) {
        $errors[] = "Username sudah terdaftar.";
    }
    if (valueExistsInUsers($pdo, 'email', $email)) {
        $errors[] = "Email sudah digunakan.";
    }
    if (valueExistsInUsers($pdo, 'npm', $npm)) {
        $errors[] = "NPM sudah terdaftar.";
    }

    return $errors;
}

/**
 * Insert a new mahasiswa user with status = 'pending'.
 * Returns the new user's id. Throws on failure (PDO::ERRMODE_EXCEPTION
 * is already set on the shared connection, so failed execute() throws
 * automatically rather than needing a manual check).
 */
function createMahasiswaUser(PDO $pdo, array $data): int
{
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (nama, npm, username, email, password, jenis_kelamin, fakultas, jurusan, no_hp, role, status)
        VALUES (:nama, :npm, :username, :email, :password, :jenis_kelamin, :fakultas, :jurusan, :no_hp, :role, :status)
    ");

    $stmt->execute([
        'nama'          => $data['nama'],
        'npm'           => $data['npm'],
        'username'      => $data['username'],
        'email'         => $data['email'],
        'password'      => $hash,
        'jenis_kelamin' => $data['jenis_kelamin'],
        'fakultas'      => $data['fakultas'],
        'jurusan'       => $data['jurusan'],
        'no_hp'         => $data['no_hp'],
        'role'          => 'mahasiswa',
        'status'        => 'pending',
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Notify the admin of a new registration by email.
 * Silently no-ops if send_email.php / kirimEmail() aren't available,
 * or if the notify-to address isn't configured.
 */
function notifyAdminNewRegistration(array $data): void
{
    $adminEmail = $_ENV['ADMIN_NOTIFY_EMAIL'] ?? getenv('ADMIN_NOTIFY_EMAIL');
    if (!$adminEmail) {
        return;
    }

    if (!function_exists('kirimEmail')) {
        return;
    }

    $subject = "Pendaftaran Mahasiswa Baru";
    $message = "Ada pengguna baru mendaftar:\n\n"
        . "Nama: {$data['nama']}\n"
        . "NPM: {$data['npm']}\n"
        . "Email: {$data['email']}\n"
        . "Jenis Kelamin: {$data['jenis_kelamin']}\n"
        . "Fakultas: {$data['fakultas']}\n"
        . "Jurusan: {$data['jurusan']}\n"
        . "No HP: {$data['no_hp']}\n"
        . "Username: {$data['username']}\n"
        . "Role: Mahasiswa\n";

    try {
        kirimEmail($adminEmail, $subject, $message);
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
    }
}
