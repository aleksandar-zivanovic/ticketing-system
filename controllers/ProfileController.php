<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'ProfileService.php';

class ProfileController extends BaseController
{
    private ProfileService $service;

    public function __construct()
    {
        $this->service = new ProfileService();
    }

    /**
     * Validates the incoming request for showing the profile.
     *
     * @return array An associative array with 'success' (bool), 'message' (string), and 'data' (array) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @see ProfileService::validateShow()
     */
    private function validateShowRequest(): array
    {
        // Validates request method and existence of $_GET["user"] data
        if (($ensureMethod = $this->ensureMethod("GET", "user", null))["success"] === false) {
            return $ensureMethod;
        }

        // Validates user ID from $_GET
        $data["id"] = $this->validateId($_GET["user"]);
        if ($data["id"] === false) {
            return ["success" => false, "message" => "Invalid user ID.", "url" => "index"];
        }

        $data["session_user_id"]   = (int) trim($_SESSION["user_id"]);
        $data["session_user_role"] = cleanString($_SESSION["user_role"]);

        return $this->service->validateShow($data);
    }

    /**
     * Renders the profile view with user data.
     *
     * @return void
     * @throws \RuntimeException If a request to the database fails.
     * @see ProfileService::validateShow()
     */
    public function show(): void
    {
        // Validates the request and handles post-validation actions
        $validation = $this->validateShowRequest();
        $this->handleValidation($validation);

        // Renders the profile view with the user data
        $this->render("profile.php", $validation["data"]);
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
    public function validateUpdateRequest(): array
    {
        // Validates request method
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method.", "url" => "index"];
        }

        // Validates if action $_POST keys are set correctly
        if (
            !isset($_POST["update_profile"]) && !isset($_POST["update_pwd"]) ||
            isset($_POST["update_profile"]) && isset($_POST["update_pwd"])
        ) {
            return ["success" => false, "message" => "Invalid action submission.", "url" => "index"];
        }

        // Validates update profile action from the form
        if (isset($_POST["update_profile"]) && trim($_POST["update_profile"]) !== "updateProfile") {
            return ["success" => false, "message" => "Invalid profile update submission.", "url" => "index"];
        }

        // Validates update password action from the form
        if (isset($_POST["update_pwd"]) && trim($_POST["update_pwd"]) !== "updatePassword") {
            return ["success" => false, "message" => "Invalid password update submission.", "url" => "index"];
        }

        // Determines action type
        $action = (isset($_POST["update_profile"]) && trim($_POST["update_profile"]) === "updateProfile") ? "updateProfile" : "updatePassword";
        $values["action"] = $action;

        // Validates user ID from session
        $IdFromSession = $this->validateId($_SESSION["user_id"]);
        if ($IdFromSession === false) {
            return ["success" => false, "message" => "Invalid user ID.", "url" => "index"];
        }
        // Creates redirection URL
        $this->redirectUrl = "/ticketing-system/public/profile.php?user={$IdFromSession}";

        // Validates profile ID from the form
        if (!$this->hasValue($_POST["profile_id"])) {
            return ["success" => false, "message" => "Profile ID is required."];
        }

        $values["id"] = $this->validateId($_POST["profile_id"]);
        if ($values["id"] === false) {
            return ["success" => false, "message" => "Invalid profile ID."];
        }

        // Sanitization and validation of email from session
        $values["emailFromSession"] = $this->validateEmail($_SESSION["user_email"]);
        if ($values["emailFromSession"] === false) {
            return ["success" => false, "message" => "Invalid email."];
        }

        if ($action === "updateProfile") {
            // Sanitization and validation of email from form (if provided)
            if (isset($_POST["email"]) && !empty(trim($_POST["email"]))) {
                $values["email"] = $this->validateEmail($_POST["email"]);
                if ($values["email"] === false) {
                    return ["success" => false, "message" => "Invalid email."];
                }
            }

            // Sanitization and validation of first name from form (if provided)
            if (isset($_POST["fname"])) {
                if (empty(trim($_POST["fname"]))) {
                    return ["success" => false, "message" => "Invalid first name."];
                }
                $values["name"] = cleanString($_POST["fname"]);
            }

            // Sanitization and validation of surname from form (if provided)
            if (isset($_POST["sname"])) {
                if (empty(trim($_POST["sname"]))) {
                    return ["success" => false, "message" => "Invalid surname."];
                }
                $values["surname"] = cleanString($_POST["sname"]);
            }

            // Sanitization and validation of phone from form (if provided)
            if (isset($_POST["phone"])) {
                if (empty(trim($_POST["phone"]))) {
                    return ["success" => false, "message" => "Invalid phone number."];
                }
                $values["phone"] = cleanString($_POST["phone"]);
            }
        }

        if ($action === "updatePassword") {
            if (
                !$this->hasValue($_POST["password_current"]) ||
                !$this->hasValue($_POST["password_new"]) ||
                !$this->hasValue($_POST["password_confirmation"])
            ) {
                return ["success" => false, "message" => "All password fields are required."];
            }

            $values["password_current"] = trim($_POST["password_current"]);
            $values["password"] = trim($_POST["password_new"]);
            $values["password_confirmation"] = trim($_POST["password_confirmation"]);
        }

        $values["session_user_id"]   = (int) trim($_SESSION["user_id"]);
        $values["session_user_role"] = cleanString($_SESSION["user_role"]);

        return $this->service->validate($values);
    }

    /**
     * Calls the service to update user data.
     *
     * @param array $data The sanitized and validated data to be updated.
     * @return void
     */
    public function update(): void
    {
        // Validates the request and handles post-validation actions
        $validation = $this->validateUpdateRequest();
        $this->handleValidation($validation);
        $data      = $validation["data"];
        $loginPage = "/ticketing-system/login.php";
        unset($data["emailFromSession"]);

        // Calls the service to update the user data
        try {
            $this->service->update($data);

            if ($data["action"] === "updatePassword") {
                $_SESSION = [];
                redirectAndDie($loginPage, "Password changed successfully. Please log in again.", "success");
            } else {
                if (isset($data["email"])) {
                    $_SESSION = [];
                    $message = "Email is updated. Go to your email to verify your new email address. Log in after email verification.";
                    redirectAndDie($loginPage, $message, "success");
                }

                redirectAndDie($this->redirectUrl, "Profile updated successfully.", "success");
            }
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Profile update failed. Please try again later.");
        }
    }
}
