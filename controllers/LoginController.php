<?php
require_once 'BaseController.php';
require_once ROOT . "services" . DS . "LoginService.php";
require_once ROOT . "Exceptions" . DS . "AccountNotVerifiedException.php";
require_once ROOT . "Exceptions" . DS . "AccountBlockedException.php";

use Exceptions\AccountNotVerifiedException;
use Exceptions\AccountBlockedException;

class LoginController extends BaseController
{
    private $service;
    private string $email;

    public function __construct()
    {
        $this->service = new LoginService();
    }

    /**
     * Validates the input data for login.
     *
     * @param array $data The input form data to validate.
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @see User::getPasswordByEmail()
     */
    public function validateRequest(): array
    {
        $this->redirectUrl = BASE_URL . "login.php";

        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            return ["success" => false, "message" => "Invalid request method."];
        }

        if (empty($_POST['user_action']) || (trim($_POST['user_action']) !== "Login")) {
            return ["success" => false, "message" => "Invalid user action."];
        }

        // Validates password
        if ($this->hasValue($_POST['password']) === false) {
            return ["success" => false, "message" => "Please fill in password."];
        }

        // Validates and sanitizes email
        if ($this->hasValue($_POST['email']) === false) {
            return ["success" => false, "message" => "Please fill in email."];
        }

        $email = $this->validateEmail($_POST['email']);
        if ($email === false) {
            return ["success" => false, "message" => "Invalid email address format."];
        }
        $this->email = $email;

        // Validation via service layer
        return $this->service->validate($email, trim($_POST['password']));
    }

    public function login(): void
    {
        $validate = $this->validateRequest();
        $this->handleValidation($validate);

        try {
            $userData = $this->service->login($this->email);

            // Set session variables
            foreach ($userData as $key => $value) {
                $_SESSION[$key] = $value;
            }

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Redirect based on user role
            $redirectData = fn($role) => [BASE_URL . "{$role}/{$role}-ticket-listing.php", "Login successful.", "success"];

            if (trim($_SESSION['user_role']) === "admin") {
                redirectAndDie(...$redirectData("admin"));
            } else {
                redirectAndDie(...$redirectData("user"));
            }
        } catch (AccountNotVerifiedException | AccountBlockedException $e) {
            redirectAndDie($this->redirectUrl, $e->getMessage(), "info");
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Login failed.");
        }
    }

    /**
     * Renders a view file and passes data to it.
     * 
     * @param string $view The view file to render (relative to the views directory).
     * @param array $data An associative array of data to extract and make available in the view.
     * @return void
     */
    public function show(string $view, array $data = []): void
    {
        $this->render($view, $data);
    }
}
