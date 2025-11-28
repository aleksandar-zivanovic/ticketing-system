<?php
require_once 'BaseService.php';
require_once 'EmailService.php';

class UserNotificationsService extends BaseService
{
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
        $linkUrl  = BASE_URL . "email-verification.php?email=" . $email . "&verification_code=" . $verificationCode;
        $linkText = "Click Here to Verify Your Email";
        $subject  = 'Verification email';

        $body     = require_once ROOT . 'EmailTemplates' . DS . 'verification_code_email.php';

        $altBody  =
            "Hello {$name} {$surname},\n" .
            "Copy the URL below into your browser's address bar and press Enter to verify your email address:\n" .
            "{$linkUrl}";

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
        $linkUrl  = BASE_URL . "profile.php?user=58";
        $linkText = "Click Here to Visit Your Profile";

        // Build the email content
        $body    = require_once ROOT . 'EmailTemplates' . DS . 'profile_update_notification_email.php';

        // Plain text alternative body
        $altBody =
            "Hello {$name} {$surname},\n" .
            "This is to inform you that your {$change} has been successfully updated.\n" .
            "If you did not make this change, please contact our support team immediately.\n" .
            "Visit our site: " . BASE_URL . "\n\n" .
            "Best regards,\n" .
            "The Ticketing System Team";

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Sends an old email change notification to the user's previous email address.
     * 
     * @param string $oldEmail The user's old email address.
     * @param string $newEmail The user's new email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @return void
     * @throws Exception If email sending fails.
     * @see EmailService::sendEmail()
     */
    public function sendOldEmailChangeNotification(string $oldEmail, string $newEmail, string $name, string $surname): void
    {
        $subject  = "Email Change Notification";
        $linkUrl  = BASE_URL . "rollback_email_change.php?email={$oldEmail}";
        $linkText = "Click Here to Cancel the Change";

        // Build the email content
        $body     = require_once ROOT . 'EmailTemplates' . DS . 'old_email_change_notification.php';

        // Plain text alternative body
        $altBody  =
            "Hello {$name} {$surname},\n" .
            "This is to inform you that your email address has been changed from {$oldEmail} to {$newEmail}.\n" .
            "If you did not make this change, please contact our support team immediately.\n" .
            "or click the following link to cancel the change: {$linkUrl}";

        // Sends the email
        $this->emailService->sendEmail(email: $oldEmail, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }
}
