<?php

class BaseService
{
    /**
     * Validates if an URL includes allowed domain(s).
     * 
     * @param string $url URL to check
     * @param array $domain Array of allowed domains
     * @return array Associative array with 'success' (bool) and 'message' (string).
     */
    protected function validateAllowedDomain(string $url, array $domain): array
    {
        if (!in_array($url, $domain)) {
            return ["success" => false, "message" => "Invalid domain"];
        }
        return ["success" => true];
    }

    /**
     * Validates text minimal length.
     * Returns true if text length is equal or greater than specified length, otherwise false.
     * @param string $text Text to validate.
     * @param string $length Minimum length required.
     * @return bool True if valid, false otherwise.
     */
    protected function validateTextLength(string $text, string $length): bool
    {
        if (strlen($text) < $length) {
            return false;
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
        require_once '../../classes/Department.php';
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
        require_once '../../classes/Priority.php';
        $priority   = new Priority();
        $priorities = $priority->getAllPriorotyIds();
        return in_array($priorityId, $priorities);
    }
}
