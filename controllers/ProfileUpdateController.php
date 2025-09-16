<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../services/ProfileUpdateService.php';

class ProfileUpdateController extends BaseController
{
    public int $sanitizedProfileId;
    public int $sanitizedIdFromSession;
    private array $sanitizedData;
    private ProfileUpdateService $service;

    public function __construct()
    {
        $this->service = new ProfileUpdateService();
    }

    /**
     * Validates the incoming request data.
     * Sanitizes and validates data and sets $sanitizedData property if validation is successful.
     *
     * @param array $data The incoming request data (e.g., $_POST).
     * @param string|int $userId The ID from session of the currently logged-in user.
     * @param string $email The email from session of the currently logged-in user.
     * @param string $action The action to be performed ("updateProfile" or "updatePassword").
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @throws \InvalidArgumentException If the email is already in use.
     */
    public function validateRequest(array $data, string|int $userId, string $email, string $action): array
    {
        // Validates action type
        if ($action !== "updateProfile" && $action !== "updatePassword") {
            return ["success" => false, "message" => "Invalid action."];
        }

        // Validates user ID from session
        $sanitizedUserId = $this->validateId($userId); // from session
        if ($sanitizedUserId === false) {
            return ["success" => false, "message" => "Invalid user ID."];
        }

        // Compares profile ID from the form with the user ID from the session
        if ($this->sanitizedProfileId !== $sanitizedUserId) {
            logError("profile_id manually changed in form on profile.php page by the user {$_SESSION['user_email']} with IP: " . getIp());
            return ["success" => false, "message" => "Can't change other user's profile."];
        }
        $this->sanitizedIdFromSession = $sanitizedUserId;

        // Sanitization and validation of email from session
        $value["emailFromSession"] = $this->validateEmail($email);
        if ($value["emailFromSession"] === false) {
            return ["success" => false, "message" => "Invalid email."];
        }

        if ($action === "updateProfile") {
            // Sanitization and validation of email from form (if provided)
            if (isset($data["email"]) && !empty(trim($data["email"]))) {
                $value["email"] = $this->validateEmail($data["email"]);
                if ($value["email"] === false) {
                    return ["success" => false, "message" => "Invalid email."];
                }
            }

            // Sanitization of profile fields
            $value["fname"] = cleanString($data["fname"]);
            $value["sname"] = cleanString($data["sname"]);

            if (isset($data["phone"]) && !empty(trim($data["phone"]))) {
                $phone = cleanString($data["phone"]);
                $regex = preg_match("/^\\+?[0-9]{7,15}$/", $phone);

                if ($regex !== 1) {
                    return ["success" => false, "message" => "Phone number must contain at least 7 and maximum 15 characters!"];
                }

                $value["phone"] = $phone;
            }
        }

        if ($action === "updatePassword") {
            $value["password_current"] = trim($data["password_current"]);
            $value["password_new"] = trim($data["password_new"]);
            $value["password_confirmation"] = trim($data["password_confirmation"]);
        }

        $value["id"] = $sanitizedUserId;
        $this->sanitizedData = $value;

        $serviceValidation = $this->service->validate($value, $sanitizedUserId, $action);
        if ($serviceValidation["success"] === false) {
            return ["success" => false, "message" => $serviceValidation["message"]];
        }

        return ["success" => true];
    }

    /**
     * Calls the service to update user data.
     *
     * @param int $userId The ID from session of the currently logged-in user.
     * @param string $action The action to be performed ("updateProfile" or "updatePassword").
     * @return void
     */
    public function update(string $action): void
    {
        unset($this->sanitizedData["emailFromSession"]);
        $this->service->update($this->sanitizedIdFromSession, $action);
    }
}
