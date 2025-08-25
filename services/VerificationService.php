<?php
require_once ROOT . 'helpers/functions.php';
require_once 'BaseService.php';
require_once 'EmailService.php';
require_once ROOT . 'classes/User.php';

class VerificationService extends BaseService
{
    private EmailService $emailService;
    private User $user;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->user = new User();
    }

    /**
     * Verifies resend request.
     * 
     * @param string $email The email to verify.
     * @return array An associative array with 'success' (bool) and 'data' (array) or 'message' (string) keys.
     * @throws RuntimeException If the database query fails.
     */
    public function validateResend(string $email): array
    {
        $theUser = $this->user->getUserByEmail($email);
        if ($theUser === false) {
            return ["success" => false, "message" => "Invalid email address."];
        }

        return [
            "success" => true,
            "data" => [
                "email" => $theUser["u_email"],
                "name" => $theUser["u_name"],
                "surname" => $theUser["u_surname"]
            ]
        ];
    }

    /**
     * Validates the verification request.
     * @param array $data An associative array containing 'email' and 'verification_code' keys.
     * 
     * @return array An associative array with 'success' (bool) and 'data' (array) or 'message' (string) keys.
     * @throws RuntimeException If the database query fails.
     * @see BaseController::validateEmail()
     */
    public function validateVerify(array $data): array
    {
        // Check if the email exists
        $isEmailOccupied = $this->user->isEmailOccupied($data["email"]);
        if ($isEmailOccupied === false) {
            return ["success" => false, "message" => "Invalid email address."];
        }

        // Validate the verification code length
        if (strlen($data["verification_code"]) !== 40) {
            return ["success" => false, "message" => "Invalid verification code format."];
        }

        // Check if the verification code for the email exists in the database
        $storedCode = $this->user->gettingUserVerificationCode($data["email"]);
        if ($storedCode === null) {
            return ["success" => false, "message" => "No verification code found for this email."];
        }

        // Compare the provided code with the stored code
        if ($storedCode !== $data["verification_code"]) {
            return ["success" => false, "message" => "Verification code does not match."];
        }

        return ["success" => true, "data" => $data];
    }

    /**
     * Build the verification email content.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $verificationCode The generated verification code.
     * @param string $action The action triggering the email ("resend", "update_email" and "registration").
     * @return array An associative array containing 'subject', 'body', and 'altBody' keys for the email.
     */
    private function buildVerificationEmail(string $email, string $name, string $surname, string $verificationCode, string $action): array
    {
        $verificationUrl  = "http://localhost/ticketing-system/email-verification.php";
        $subject          = 'Verification email';

        if ($action === "resend" || $action === "update_email") {
            // Creates email message body. You can customize the email body as needed.
            $body = 'Hello, ' . $name . ' ' . $surname . '!<br> Click on this link:  <a href="' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '">' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '</a></b> to verify your email address.';

            // Creates plain text alternative body for email clients that do not support HTML.
            $altBody = 'Copy this URL in your broswer navigation bar and click enter to verify your email address: href="' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '">' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode;
        } elseif ($action === "registration") {
            // Creates email message body. You can customize the email body as needed.
            $body = 'Welcome ' . $name . ' ' . $surname . '!<br> Thank you for registering. Please click on this link:  <a href="' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '">' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '</a></b> to verify your email address and activate your account.';

            // Creates plain text alternative body for email clients that do not support HTML.
            $altBody = 'Copy this URL in your broswer navigation bar and click enter to verify your email address: href="' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode . '">' . $verificationUrl . '?email=' . $email . '&verification_code=' . $verificationCode;
        }

        return ['subject' => $subject, 'body' => $body, 'altBody' => $altBody];
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
    public function sendNow(string $email, string $name, string $surname, string $action): void
    {
        $verificationCode = $this->generateVerificationCode();

        // Store the verification code in the database
        $this->user->addVerificationCodeToUser($verificationCode, $email);

        // Marks user as unverified when changing email
        if ($action === "update_email") {
            $this->user->markAsUnverified($email);
        }

        // Build the email content
        ['subject' => $subject, 'body' => $body, 'altBody' => $altBody] = $this->buildVerificationEmail($email, $name, $surname, $verificationCode, $action);

        // Sends the email
        $this->emailService->sendEmail(email: $email, name: $name, surname: $surname, subject: $subject, body: $body, altBody: $altBody);
    }

    /**
     * Marks the user as verified by setting the verified field to 1 and clearing the verification code.
     * 
     * @param string $email The email of the user to update.
     * @return bool Returns true if the update was successful, false otherwise.
     * @throws RuntimeException If the database update fails.
     * @see User::makeUserVerified()
     */
    public function verifyUser(string $email): bool
    {
        return $this->user->makeUserVerified($email);
    }
}
