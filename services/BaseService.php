<?php

class BaseService
{
    /**
     * Validates the length of a text string.
     * 
     * @param string $text The text to validate.
     * @param int|null $minLength Minimum length (inclusive). If null, no minimum length check is performed.
     * @param int|null $maxLength Maximum length (inclusive). If null, no maximum length check is performed.
     * @return bool True if the text meets the length requirements, false otherwise.
     */
    protected function validateTextLength(string $text, ?int $minLength = null, ?int $maxLength = null): bool
    {
        if ($minLength === null && $maxLength === null) {
            return false;
        }

        if ($minLength !== null) {
            if (strlen($text) < $minLength) {
                return false;
            }
        }

        if ($maxLength !== null) {
            if (strlen($text) > $maxLength) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a department ID exists among existing departments.
     * @param int $departmentId ID to validate.
     * @return bool Valid ID or false if invalid.
     */
    protected function validateDepartments(int $departmentId): bool
    {
        require_once ROOT . 'classes' . DS . 'Department.php';
        $department  = new Department();
        $departments = $department->getAllDepartmentIds();
        return in_array($departmentId, $departments);
    }

    /**
     * Checks if a priority ID exists among existing priorities.
     * @param int $priorityId ID to validate.
     * @return bool Valid ID or false if invalid.
     */
    protected function validatePriorities(int $priorityId): bool
    {
        require_once ROOT . 'classes' . DS . 'Priority.php';
        $priority   = new Priority();
        $priorities = $priority->getAllPriorotyIds();
        return in_array($priorityId, $priorities);
    }

    /**
     * Validates the existence of a user by ID.
     *
     * @param array $data The data containing the user ID to validate.
     * @return array An associative array with 'success' (bool), 'message' (string), and 'data' (array) keys.
     * @throws \RuntimeException If a request to the database fails.
     * @see User::getAllWhere()
     */
    protected function validateUserExistence(array $data, User $userModel): array
    {
        $theUser = $userModel->getAllWhere("users", "id = {$data['id']}")[0];
        if (empty($theUser)) {
            return ["success" => false, "message" => "User not found.", "url" => "index"];
        }

        return [
            "success" => true,
            "data" => [
                "theUser" => $theUser,
            ]
        ];
    }

    /**
     * Validates a phone number.
     * The phone number can optionally start with a '+' and must contain between 7 and 15 digits.
     *
     * @param string $phone The phone number to validate.
     * @return bool True if the phone number is valid, false otherwise.
     */
    protected function validatePhone(string $phone): bool
    {
        return preg_match("/^\\+?[0-9]{7,15}$/", $phone) === 1;
    }

    /**
     * Hashes a password using a strong one-way hashing algorithm.
     *
     * @param string $password The password to hash.
     * @return string The hashed password.
     */
    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Generates a random verification code.
     *
     * @return string The generated verification code.
     */    protected function generateVerificationCode(): string
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * Fetches departments and priorities from the Ticket model.
     * 
     * @param Ticket $ticketModel An instance of the Ticket model.
     * @return array Associative array containing 'departments' and 'priorities'.
     * @throws RuntimeException If there is a PDOException while executing the SQL query.
     * @see Ticket::getAllDepartments()
     * @see Ticket::getAllPriorities()
     */
    protected function getAllDepartmentsAndPriorities(Ticket $ticketModel): array
    {
        $departments = $ticketModel->getAllDepartments();
        $priorities  = $ticketModel->getAllPriorities();

        return [
            "departments" => $departments,
            "priorities"  => $priorities
        ];
    }
}
