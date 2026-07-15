<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/phpmailer/src/Exception.php';

// Sends an email via SMTP using credentials from .env (never hardcode
// secrets here — this file is committed to git).
// Returns true on success, false on failure (logged via error_log rather
// than echoed, so a failed notification email never breaks the page).
function kirimEmail(string $to, string $subject, string $message): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = (int) env('MAIL_DEBUG', 0);
        $mail->Debugoutput = 'error_log';

        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME', '');
        $mail->Password   = env('MAIL_PASSWORD', '');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
        $mail->Port       = (int) env('MAIL_PORT', 587);

        $mail->setFrom(env('MAIL_FROM_ADDRESS', $mail->Username), env('MAIL_FROM_NAME', 'SIMKM'));
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br(htmlspecialchars($message));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('kirimEmail failed: ' . $mail->ErrorInfo);
        return false;
    }
}
