<?php
require_once __DIR__ . '/../classes/User.php';

class ProfileUpdateService
{
    private User $user;
    private array $preparedData;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Validates the input data for profile update or password change.
     *
     * @param array $data The input data to validate.
     * @param int $userId The ID of the user performing the action.
     * @param string $action The action to perform ("updateProfile" or "updatePassword").
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @throws \InvalidArgumentException If the email is already in use.
     * @see User::isEmailOccupied()
     * @see User::getPasswordByEmail()
     * @see ProfileUpdateService::doesUserExist()
     */
    public function validate(array $data, int $userId, string $action): array
    {
        // Checks if email from session matches any of the emails in the database
        if (!$this->doesUserExist($userId, $data["emailFromSession"])) {
            return ["success" => false, "message" => "User validation failed."];
        }

        if ($action === "updateProfile") {
            // Checks if the first name is at least 3 characters long if provided
            if (isset($data["fname"])) {
                if (strlen($data["fname"]) < 3) {
                    return ["success" => false, "message" => "First name must be at least 3 characters long."];
                }
            }
            $this->preparedData["name"] = $data["fname"];

            // Checks if the surname is at least 3 characters long if provided
            if (isset($data["sname"])) {
                if (strlen($data["sname"]) < 3) {
                    return ["success" => false, "message" => "Surname must be at least 3 characters long."];
                }
            }
            $this->preparedData["surname"] = $data["sname"];

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
                $this->preparedData["email"] = $data["email"];
            }

            if (isset($data["phone"])) {
                $this->preparedData["phone"] = $data["phone"];
            }

            $this->preparedData["id"] = $data["id"];
        }

        if ($action === "updatePassword") {
            // Password validation logic can be added here
            if (
                strlen($data["password_new"]) < 6 ||
                strlen($data["password_confirmation"]) < 6 ||
                strlen($data["password_current"]) < 6
            ) {
                return ["success" => false, "message" => "All passwords must be at least 6 characters long."];
            }

            // Checks if new password and confirmation match
            if ($data["password_new"] !== $data["password_confirmation"]) {
                return ["success" => false, "message" => "New password and confirmation password don't match."];
            }

            // Checks if new password is different from the current password
            if ($data["password_new"] === $data["password_current"]) {
                return ["success" => false, "message" => "New password must be different from the old."];
            }
            $this->user->setPassword($data["password_new"]);

            // Verifies the current (old) password
            $currentPasswordHash = $this->user->getPasswordByEmail($data["emailFromSession"]);
            if (
                $currentPasswordHash === null ||
                !password_verify($data["password_current"], $currentPasswordHash)
            ) {
                return ["success" => false, "message" => "Current password is incorrect."];
            }
        }

        return ["success" => true];
    }

    /**
     * Validates the user by checking the user ID from form and email from session in the database.
     *
     * @param int $userId The ID of the user to validate.
     * @param string $email The email from session.
     * @return bool True if the user is valid, false otherwise.
     * @see User::getUserById()
     */
    private function doesUserExist(int $userId, string $email): bool
    {
        $userDetails = $this->user->getUserById($userId);
        if (empty($userDetails) || $userDetails["email"] !== $email) {
            return false;
        }
        return true;
    }

    public function update(int $userId, string $action): void
    {
        if(isset($this->preparedData["email"])) {
            // TODO: send confirmation code to new email address
            // TODO: send notification email to old email address
            // TODO: make email_change_requests table
        }
        if ($action === "updateProfile") {
            $this->user->updateUserRow($this->preparedData, $userId);
        }

        if ($action === "updatePassword") {
            $this->user->updatePassword($userId);
        }
    }
}
