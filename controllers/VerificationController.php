<?php
require_once 'BaseController.php';
require_once ROOT . 'services/VerificationService.php';

class VerificationController extends BaseController
{
    private VerificationService $service;
    private string $sanitizedEmail = '';

    public function __construct()
    {
        $this->service = new VerificationService();
    }

    /**
     * Validates the resend request.
     * 
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws RuntimeException If the database query fails.
     * @see BaseController::validateEmail()
     */
    public function validateResendRequest(): array
    {
        $this->redirectUrl = "/ticketing-system/resend-code.php";

        if (
            $_SERVER['REQUEST_METHOD'] !== "POST" ||
            !isset($_POST['verification_code_form']) ||
            trim($_POST['verification_code_form']) !== "Send new code"
        ) {
            return ["success" => false, "message" => "Invalid request method or form submission."];
        }

        if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
            return ["success" => false, "message" => "Email is required."];
        }

        $this->sanitizedEmail = $this->validateEmail($_POST['email']);
        if ($this->sanitizedEmail === false) {
            return ["success" => false, "message" => "Invalid email format."];
        }

        return $this->service->validateResend($this->sanitizedEmail);
    }

    /**
     * Send a verification email to the user.
     * 
     * @param string $action The action type, either "resend" or "initial".
     * @return void
     * @throws RuntimeException If sending the email fails.
     * @see VerificationService::sendNow()
     */
    public function sendVerificationEmail(string $action): void
    {
        $this->render("resend_code.php");
        // Validate the resend request
        $validated = $this->validateResendRequest();
        $this->handleValidation($validated);

        // Send the email
        try {
            $this->service->sendNow($this->sanitizedEmail, $validated['data']['name'], $validated['data']['surname'], $action);
            redirectAndDie($this->redirectUrl, "Verification code has been sent to your email.", "success");
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Failed to send verification email. Please try again later.");
        }
    }

    /**
     * Validates the verification request.
     * 
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws RuntimeException If the database query fails.
     * @see BaseController::validateEmail()
     */
    private function validateVerifyRequest(): array
    {
        if ($this->hasValue($_GET["email"]) === false) {
            return ["success" => false, "message" => "Email is required."];
        }

        $data["email"] = $this->validateEmail($_GET["email"]);
        if ($data["email"] === false) {
            return ["success" => false, "message" => "Invalid email format."];
        }

        if ($this->hasValue($_GET["verification_code"]) === false) {
            return ["success" => false, "message" => "Verification code is required."];
        }

        $data["verification_code"] = cleanString($_GET["verification_code"]);

        return $this->service->validateVerify($data);
    }

    /**
     * Verifies the user based on the verification request.
     * 
     * @return void
     * @throws RuntimeException If the database query fails.
     * @see VerificationService::verifyUser()
     */
    public function verifyUser(): void
    {
        $this->redirectUrl = "/ticketing-system/resend-code.php";
        $validated = $this->validateVerifyRequest();
        $this->handleValidation($validated);

        try {
            $this->service->verifyUser($validated["data"]["email"]);
            redirectAndDie($this->redirectUrl, "Email verified successfully.", "success");
        } catch (\Throwable $th) {
            redirectAndDie($this->redirectUrl, "Failed to verify email. Please try again later.");
        }
    }

    /**
     * Displays a view with optional data.
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
