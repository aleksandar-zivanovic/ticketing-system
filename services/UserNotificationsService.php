<?php
require_once 'BaseService.php';
require_once 'EmailService.php';

class UserNotificationsService extends BaseService
{
    // This class can be expanded with methods to handle user notifications
    // +verifikacioni emailovi, promena podataka od strane korisnika i promena role od strane administratora

    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Send a verification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $action The action triggering the email ("resend", "update_email" and "registration").
     * @return void
     * @throws RuntimeException If the database update fails.
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     * @see User::addVerificationCodeToUser()
     */
    public function sendVerificationEmail(
        string $email,
        string $name,
        string $surname,
        string $verificationCode,
        string $action
    ): void {
        $verificationUrl  = "http://localhost/ticketing-system/email-verification.php";
        $subject          = 'Verification email';

        // // Build the email content
        // ['subject' => $subject, 'body' => $body, 'altBody' => $altBody] = $this->buildVerificationEmail($email, $name, $surname, $verificationCode, $action);

        $body = require_once ROOT . 'EmailTemplates' . DS . 'verification_code_email.php';

        $altBody =
            "Hello {$name} {$surname},\n" .
            "Copy the URL below into your browser's address bar and press Enter to verify your email address:\n" .
            "{$verificationUrl}?email={$email}&verification_code={$verificationCode}";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Sends a profile update notification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $action The action triggering the email ("updateProfile" or "updatePassword").
     * @return void
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     */
    public function sendProfileUpdateNotificationEmail(
        string $email,
        string $name,
        string $surname,
        string $action
    ): void {
        // Determine the type of change
        if ($action === "updateProfile") {
            $change = "profile";
        } elseif ($action === "updatePassword") {
            $change = "password";
        }

        $subject = ucfirst($change) . " Update Notification";
        $siteUrl = "http://localhost/ticketing-system/";

        // Build the email content
        $body    = require_once ROOT . 'EmailTemplates' . DS . 'profile_update_notification_email.php';

        // Plain text alternative body
        $altBody =
            "Hello {$name} {$surname},\n" .
            "This is to inform you that your {$change} has been successfully updated.\n" .
            "If you did not make this change, please contact our support team immediately.\n" .
            "Visit our site: {$siteUrl}";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }
}
