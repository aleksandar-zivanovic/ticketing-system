<?php

class BaseController
{
    /**
     * Checks if a string variable, assigned from $_POST, is defined and not empty.
     *
     * @param string $variable The variable to check.
     * @return bool True if the variable exists and is not empty, false otherwise.
     */
    public function hasValue(string $value): bool
    {
        return isset($value) && !empty($value);
    }

    /**
     * Validates and sanitize an ID to ensure it is a positive integer.
     * 
     * @param int|string $id The ID to validate.
     * @return int|false The validated ID as an integer, or false if invalid.
     */
    public function validateId(int|string $id): int|false
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if ($id === false) return false;
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id;
    }

    /**
     * Validates and sanitize an URL.
     */
    public function validateUrl(string $url): string|false
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = filter_var($url, FILTER_VALIDATE_URL);

        return $url;
    }
}
