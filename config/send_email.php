<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/phpmailer/src/Exception.php';

function kirimEmail($to, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        // DEBUG (WAJIB SAAT TEST)
        $mail->SMTPDebug = 2; 
        $mail->Debugoutput = 'html';

        // SMTP CONFIG
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'addoafrilioputera@gmail.com';

        // ❗ HAPUS SPASI APP PASSWORD
        $mail->Password   = 'cpkievpfstmebcbo';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // FROM & TO
        $mail->setFrom('addoafrilioputera@gmail.com', 'SIMKM XAMPP');
        $mail->addAddress($to);

        // CONTENT
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($message);

        $mail->send();
        return true;

    } catch (Exception $e) {
        // TAMPILKAN ERROR (INI YANG PENTING)
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
        exit;
    }
}