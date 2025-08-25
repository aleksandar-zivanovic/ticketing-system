<?php
require_once 'BaseService.php';
require_once ROOT . "classes" . DS . "User.php";

use Exceptions\AccountNotVerifiedException;

class LoginService extends BaseService
{
    private User $user;
    public function __construct()
    {
        $this->user = new User();
    }


    /**
     * Validates the input data for login.
     *
     * @param string $email The email address to validate.
     * @param string $password The password to validate.
     * @return array An associative array with 'success' (bool) and 'message' (string) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @see User::getPasswordByEmail()
     */
    public function validate(string $email, string $password)
    {
        // Checks if the password is at least 6 characters long
        if ($this->validateTextLength($password, 6) === false) {
            return ["success" => false, "message" => "Password must be at least 6 characters long."];
        }

        // Checks if email exists in the database and fetch that user record if it does
        $hashedPassword = $this->user->getPasswordByEmail($email);
        if ($hashedPassword === null) {
            return ["success" => false, "message" => "Email address not found."];
        }

        // Verifies the provided password against the hashed password from the database
        if (password_verify($password, $hashedPassword) === false) {
            return ["success" => false, "message" => "Invalid password."];
        }

        return ["success" => true, "email" => $email];
    }


    public function login($email): array
    {
        $user = $this->user->getUserByEmail($email);

        // Check if the account is verified
        if ($user['u_verified'] !== 1) {
            throw new AccountNotVerifiedException();
        }

        // Update session version to invalidate other sessions
        $newSession = $user["u_session_version"] + 1;
        $this->user->updateRows('users', [["session_version" => $newSession]], [["id" => $user["u_id"]]]);

        // Initializing session data for the authenticated user
        return [
            "user_email"      => $user["u_email"],
            "user_id"         => $user["u_id"],
            "user_name"       => $user["u_name"],
            "user_surname"    => $user["u_surname"],
            "user_role"       => $user["r_name"],
            "user_phone"      => $user["u_phone"],
            "user_department" => $user["d_name"],
            "isVerified"      => true,
            "last_check"      => time(),
            "session_version" => $newSession
        ];
    }
}
