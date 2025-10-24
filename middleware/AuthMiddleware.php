<?php

class AuthMiddleware
{
    private array $publicPaths = [
        "/login.php",
        "/login_action.php",
        "/register.php",
        "/register_action.php",
        "/logout.php",
        "/resend-code.php",
        "/resend_code_action.php",
        "/email-verification.php",
        "/tests/"
    ];

    private array $rolePaths = [
        "/admin-ticket-listing"      => "admin",
        "/admin-tickets-i-handle"    => "admin",
        "/admin/view-ticket"         => "admin",
        "/admin/split-ticket"        => "admin",
        "/ticket_close_action"       => "admin",
        "/ticket_reopen_action"      => "admin",
        "/ticket_delete_action"      => "admin",
        "/take_ticket_action"        => "admin",
        "/admin-edit-message"        => "admin",
        "/admin-dashboard.php"       => "admin",
        "/admin/users"               => "admin",
    ];

    /**
     * Check if the given URL is a public path.
     * 
     * @param string $url The URL to check. Received from front controller.
     * @return bool
     */
    public function isPublicPath(string $url): bool
    {
        foreach ($this->publicPaths as $path) {
            if (str_contains($url, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the given URL requires a specific user role.
     * 
     * @param string $url The URL to check. Received from front controller.
     * @return string|false The required role or false if no specific role is required.
     */
    private function checkRolePath(string $url): string|false
    {
        foreach ($this->rolePaths as $path => $roleRequired) {
            if (str_contains($url, $path)) {
                return $roleRequired;
            }
        }
        return false;
    }

    /**
     * Handle authentication and authorization based on the given URL.
     * 
     * @param string $url The URL to handle. Received from front controller.
     * @return void
     */
    public function handle(string $url): void
    {
        if ($this->isPublicPath($url) === false) {
            requireLogin();

            $role = $this->checkRolePath($url);

            if ($role !== false && $_SESSION['user_role'] !== $role) {
                redirectAndDie("index.php", "Access denied. " . ucfirst($role) . "s only.");
            }
        }
    }
}
