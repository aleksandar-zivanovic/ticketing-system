<?php
require_once ROOT . 'classes' . DS . 'User.php';
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'VerificationService.php';
require_once ROOT . 'services' . DS . 'UserNotificationsService.php';

class ProfileService extends BaseService
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    private function vaildateCommon(array $data, array $theUser): array
    {
        // Compares the user ID from the URL with the user ID from the session
        if ($data["id"] !== $data["session_user_id"]) {

            if ($data["session_user_role"] !== "admin") {
                // Admins can see other users' profiles
                logError("tried to view or update user ID: {$data["id"]} profile by the user {$_SESSION['user_email']} with IP: " . getIp());
                return ["success" => false, "message" => "Can't view or update other user's profile.", "url" => "index"];
            }

            // Admins cannot see other admins' profiles
            if ($theUser["role_id"] === USER_ROLES["admin"]) {
                return ["success" => false, "message" => "Can't view or update other admin's profile.", "url" => "index"];
            }
        }
        return ["success" => true];
    }

    /**
     * Validates the existence of a user by ID.
     *
     * @param array $data The data containing the user ID to validate.
     * @return array An associative array with 'success' (bool), 'message' (string), and 'data' (array) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @see User::getAllWhere()
     */
    public function validateShow($data): array
    {
        // Validates user existence in the database
        $validateUserExistence = $this->validateUserExistence($data, $this->user);
        if ($validateUserExistence["success"] === false) {
            return $validateUserExistence;
        }
        $theUser = $validateUserExistence["data"]["theUser"];


        // Checks if the user is trying to view/update another user's profile and 
        // if the session user has the necessary permissions to do so
        $vaildateCommon = $this->vaildateCommon($data, $theUser);
        if ($vaildateCommon["success"] === false) {
            return $vaildateCommon;
        }

        return [
            "success" => true,
            "data" => [
                "id" => $data["id"],
                "theUser" => $theUser,
                "session_user_id" => $data["session_user_id"],
            ]
        ];
    }

    /**
     * Validates the input data for profile update or password change.
     *
     * @param array $data The input data to validate.
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @throws \InvalidArgumentException If the email is already in use.
     * @see User::isEmailOccupied()
     * @see User::getPasswordByEmail()
     * @see ProfileService::doesUserExist()
     */
    public function validate(array $data): array
    {
        // Validates user existence in the database
        $vaildateCommon = $this->validateUserExistence($data, $this->user);
        if ($vaildateCommon["success"] === false) {
            return $vaildateCommon;
        }

        if ($data["action"] === "updateProfile") {
            // Checks if the user is trying to view/update another user's profile and 
            // if the session user has the necessary permissions to do so
            $vaildateCommon = $this->vaildateCommon($data, $vaildateCommon["data"]["theUser"]);
            if ($vaildateCommon["success"] === false) {
                return $vaildateCommon;
            }

            // Checks if the first name is at least 3 characters long if provided
            if (isset($data["name"])) {
                if (strlen($data["name"]) < 3) {
                    return ["success" => false, "message" => "First name must be at least 3 characters long."];
                }
            };

            // Checks if the surname is at least 3 characters long if provided
            if (isset($data["surname"])) {
                if (strlen($data["surname"]) < 3) {
                    return ["success" => false, "message" => "Surname must be at least 3 characters long."];
                }
            }

            // Validates email if provided
            if (isset($data["email"])) {
                // Checks if the new email is different from the current email
                if ($data["emailFromSession"] === $data["email"]) {
                    return ["success" => false, "message" => "Email is the same as the current one."];
                }

                // Checks if a new email is already occupied by another user
                if ($this->user->isEmailOccupied($data["email"]) === true) {
                    return ["success" => false, "message" => "Email is already in use."];
                }
            }

            if (isset($data["phone"])) {
                if ($this->validatePhone($data["phone"]) === false) {
                    return ["success" => false, "message" => "Phone number is invalid."];
                }
            }
        }

        if ($data["action"] === "updatePassword") {
            // Prevents users from changing other users' passwords
            if ($data["session_user_id"] !== $data["id"]) {
                logError("tried to change user ID: {$data["id"]} password by the user {$_SESSION['user_email']} with IP: " . getIp());
                return ["success" => false, "message" => "Can't change other user's password."];
            }

            // Password length validation
            if (
                strlen($data["password"]) < 6 ||
                strlen($data["password_confirmation"]) < 6 ||
                strlen($data["password_current"]) < 6
            ) {
                return ["success" => false, "message" => "All passwords must be at least 6 characters long."];
            }

            // Checks if new password and confirmation match
            if ($data["password"] !== $data["password_confirmation"]) {
                return ["success" => false, "message" => "New password and confirmation password don't match."];
            }

            // Checks if new password is different from the current password
            if ($data["password"] === $data["password_current"]) {
                return ["success" => false, "message" => "New password must be different from the old."];
            }

            // Verifies the current (old) password
            $currentPasswordHash = $this->user->getPasswordByEmail($data["emailFromSession"]);
            if (
                $currentPasswordHash === null ||
                !password_verify($data["password_current"], $currentPasswordHash)
            ) {
                return ["success" => false, "message" => "Current password is incorrect."];
            }
        }

        return ["success" => true, "data" => $data];
    }

    /**
     * Updates the user's profile information or password.
     *
     * @param array $data The data to update, including 'action' key to specify the type of update.
     * @param string $notificationEmail The email address to send notifications to.
     * @return void
     * @throws \RuntimeException If a request to the database fails.
     * @see User::updateUserRow()
     * @see User::updatePassword()
     * @see VerificationService::sendNow()
     */
    public function update(array $data, string $notificationEmail): void
    {
        $action = $data["action"];
        unset($data["action"]);

        if ($action === "updateProfile") {
            // Removes surplus data from $data array for the updateUserRow() method
            unset(
                $data["session_user_id"],
                $data["session_user_role"],
                $data["notificationEmail"]
            );

            $this->user->updateUserRow($data, $data["id"]);

            // Updates session variables
            $_SESSION["user_name"]    = $data["name"];
            $_SESSION["user_surname"] = $data["surname"];
            $_SESSION["user_phone"]   = $data["phone"];

            if (isset($data["email"])) {
                // Initiates the verification service to handle email change verification
                $verificationService = new VerificationService();
                // Sends verification email to the new email address
                $verificationService->sendNow($data["email"], $data["name"], $data["surname"], "update_email");
                // DONE: send confirmation code to new email address
                // TODO: send notification email to old email address
                // TODO: make email_change_requests table
            }
        }

        if ($action === "updatePassword") {
            $hashedPassword = $this->hashPassword($data["password"]);
            $this->user->updatePassword($data["id"], $hashedPassword);
        }

        // Sends a profile/password update notification email if the email is not being changed
        if (!isset($data["email"])) {
            $userNotificationsService = new UserNotificationsService();
            $userNotificationsService->sendProfileUpdateNotificationEmail($notificationEmail, $data["name"], $data["surname"], $action);
        }
    }
}
