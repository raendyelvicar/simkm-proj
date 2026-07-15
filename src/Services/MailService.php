<?php

namespace App\Services;

class MailService
{
    public function sendWelcomeEmail(string $toEmail, string $toName): bool
    {
        $subject = 'Welcome!';
        $message = "Hi {$toName},\n\nThanks for signing up.";
        $headers = 'From: no-reply@' . ($_ENV['APP_URL'] ?? 'example.com');

        // In production, swap this out for PHPMailer, Symfony Mailer, or an API (SES, Postmark, etc.)
        return mail($toEmail, $subject, $message, $headers);
    }
}
