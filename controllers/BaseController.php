<?php

class BaseController
{
    protected ?string $redirectUrl = null;

    /**
     * Checks if a string variable, assigned from $_POST, is defined and not empty.
     *
     * @param string $variable The variable to check.
     * @return bool True if the variable exists and is not empty, false otherwise.
     */
    public function hasValue(string $value): bool
    {
        return isset($value) && !empty(trim($value));
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
     * @param string $url The URL to validate.
     * @return string|false The validated URL, or false if invalid.
     */
    public function validateUrl(string $url): string|false
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = filter_var($url, FILTER_VALIDATE_URL);

        return $url;
    }

    /**
     * Validates and sanitize an email address.
     * @param string $email The email address to validate.
     * @return string|false The validated email address, or false if invalid.
     */
    public function validateEmail(string $email): string|false
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        return $email;
    }

    /**
     * Handles post-validation actions such as redirection based on validation results.
     * 
     * @param array $validation The result of the validation process.
     * @return void
     */
    protected function handleValidation(array $validation): void
    {
        if ($validation["success"] === false) {
            if ((isset($validation["url"]) && $validation["url"] === "index") || $this->redirectUrl === null) {
                redirectAndDie("/ticketing-system/public/index.php", $validation["message"]);
            } else {
                redirectAndDie($this->redirectUrl, $validation["message"]);
            }
        }
    }

    /**
     * Renders a view file and passes data to it.
     * 
     * @param string $view The view file to render (relative to the views directory).
     * @param array $data An associative array of data to extract and make available in the view.
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        if (!empty($data)) {
            extract($data);
        }

        require_once ROOT . 'views' . DS . $view;
    }

    /**
     * Ensures that the request method and a specific parameter are present and valid.
     * 
     * @param string $method The expected HTTP method (e.g., "POST", "GET").
     * @param string $methodParam The parameter to check in the request data.
     * @param string|null $expectedValue An optional expected value for the parameter (only for POST requests).
     * @return array An array indicating success or failure, with an optional message and URL for redirection.
     */
    protected function ensureMethod(string $method, string $methodParam, ?string $expectedValue = null): array
    {
        $method = strtoupper($method);
        if ($method === "POST") {
            $data = $_POST;
        } elseif ($method === "GET") {
            $data = $_GET;
        } else {
            return ["success" => false, "message" => "Unsupported request method.", "url" => "index"];
        }

        if (
            $_SERVER["REQUEST_METHOD"] !== $method ||
            empty($data[$methodParam]) ||
            ($method === "POST" && $data[$methodParam] !== $expectedValue)
        ) {
            return ["success" => false, "message" => "Invalid request method or user action.", "url" => "index"];
        }
        return ["success" => true];
    }
}
