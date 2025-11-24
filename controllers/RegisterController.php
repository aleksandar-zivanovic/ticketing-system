<?php
require_once ROOT . 'controllers' . DS . 'BaseController.php';
require_once ROOT . 'services' . DS . 'RegisterService.php';

class RegisterController extends BaseController
{
    private RegisterService $service;

    public function __construct()
    {
        $this->service = new RegisterService();
    }

    /**
     * Redirects logged-in users to their profile page.
     * 
     * @return void
     */
    protected function redirectLoggedIn(): void
    {
        if (isLoggedIn()) {
            redirectAndDie("/ticketing-system/profile.php?user=" . cleanString($_SESSION["user_id"]), "You are already registered.", "info");
        }
    }

    /**
     * Displays the registration form.
     * 
     * @return void
     */
    public function show(): void
    {
        // Redirect if already logged in
        $this->redirectLoggedIn();

        // Render the registration form
        $this->render("register.php");
    }

    /**
     * Validates the registration request data.
     * 
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     */
    public function validateRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Invalid request method."];
        }

        if (!isset($_POST['user_action']) || $_POST['user_action'] !== 'Register') {
            return ["success" => false, "message" => "Invalid action."];
        }

        if (
            $this->hasValue($_POST['email']) === false ||
            $this->hasValue($_POST['password']) === false ||
            $this->hasValue($_POST['rpassword']) === false ||
            $this->hasValue($_POST['name']) === false ||
            $this->hasValue($_POST['surname']) === false ||
            $this->hasValue($_POST['phone']) === false
        ) {
            return ["success" => false, "message" => "All fields are required."];
        }

        if (!isset($_POST['agree_terms']) || $_POST['agree_terms'] !== 'on') {
            return ["success" => false, "message" => "You must agree to the terms and conditions."];
        }

        $email = $this->validateEmail($_POST['email']);
        if ($email === false) {
            return ["success" => false, "message" => "Invalid email format."];
        }

        $name     = cleanString($_POST['name']);
        $surname  = cleanString($_POST['surname']);
        $phone    = cleanString($_POST['phone']);

        if ($_POST['password'] !== $_POST['rpassword']) {
            return ["success" => false, "message" => "Passwords do not match."];
        }

        $values = [
            "name"     => $name,
            "surname"  => $surname,
            "email"    => $email,
            "phone"    => $phone,
            "password" => $_POST['password'],
            "rpassword" => $_POST['rpassword']
        ];

        return $this->service->validate($values);
    }

    /**
     * Registers a new user.
     * 
     * @param array $data Associative array containing the registration data.
     * 
     * @return void
     * @throws RuntimeException if the registration fails.
     * @throws Exception If email sending fails.
     * @see VerificationService::sendNow()
     */
    public function register(): void
    {
        // Redirect if already logged in
        $this->redirectLoggedIn();

        // Save form values to session except passwords
        saveFormValuesToSession(['password', 'rpassword']);

        // Set redirect URL in case of failure
        $this->redirectUrl = "/ticketing-system/forms/register.php";

        $validation = $this->validateRequest();
        $this->handleValidation($validation);

        try {
            $this->service->register($validation["data"]);
            redirectAndDie(
                $this->redirectUrl,
                "Registration successful! Please check your email to verify your account.",
                "success"
            );
        } catch (Throwable $th) {
            redirectAndDie($this->redirectUrl, "Registration failed. Please try again.");
        }
    }
}
