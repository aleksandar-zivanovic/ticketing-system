<?php
require_once ROOT . 'helpers' . DS . 'functions.php';
require_once 'BaseService.php';
require_once 'UserNotificationsService.php';
require_once ROOT . 'classes' . DS . 'User.php';

class VerificationService extends BaseService
{
    private User $user;

    public function __construct()
    {
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
     * Send a verification email to the user.
     * 
     * @param string $email The user's email address.
     * @param string $name The user's first name.
     * @param string $surname The user's surname.
     * @param string $action The action triggering the email ("resend", "update_email" and "registration").
     * @return void
     * @throws RuntimeException If the database update fails.
     * @throws Exception If email sending fails.
     * @see UserNotificationsService::sendVerificationEmail()
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

        $userNotificationsService = new UserNotificationsService();
        $userNotificationsService->sendVerificationEmail($email, $name, $surname, $verificationCode, $action);
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
