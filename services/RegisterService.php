<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'services' . DS . 'VerificationService.php';
require_once ROOT . 'classes' . DS . 'User.php';

class RegisterService extends BaseService
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Validates the registration data.
     * 
     * @param array $data Associative array containing the registration data.
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     */
    public function validate(array $data): array
    {
        // Checks if a user with the provided email already exists
        if ($this->user->isEmailOccupied($data["email"]) === true) {
            return ["success" => false, "message" => "Email is already in use."];
        }

        // Validates the length of the name (minimum 3 characters and maximum 50 characters)
        if ($this->validateTextLength($data["name"], 3, 50) === false) {
            return ["success" => false, "message" => "Name must be between 3 and 50 characters long."];
        }

        // Validates the length of the surname (minimum 3 characters and maximum 50 characters)
        if ($this->validateTextLength($data["surname"], 3, 50) === false) {
            return ["success" => false, "message" => "Surname must be between 3 and 50 characters long."];
        }

        // Validates the phone number (must contain only digits and can optionally start with a '+')
        if ($this->validatePhone($data["phone"]) === false) {
            return ["success" => false, "message" => "Invalid phone number. Phone number must contain between 7 and 15 digits and can optionally start with a '+'."];
        }

        // Validates the length of the password (minimum 6 characters)
        if ($this->validateTextLength($data["password"], 6) === false) {
            return ["success" => false, "message" => "Password must be at least 6 characters long."];
        }

        return ["success" => true, "data" => $data];
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
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
     * @see BaseService::hashPassword()
     * @see User::create()
     */
    public function register(array $data): void
    {
        $preparedData = [
            "email"    => $data['email'],
            "password" => $this->hashPassword($data["password"]),
            "name"     => $data['name'],
            "surname"  => $data['surname'],
            "phone"    => $data['phone'],
            "verification_code" => $this->generateVerificationCode(),
        ];

        // Create the user in the database
        $this->user->create($preparedData);

        // Send verification email
        $verificationService = new VerificationService();
        $verificationService->sendNow($data['email'], $data['name'], $data['surname'], "registration");
    }
}
