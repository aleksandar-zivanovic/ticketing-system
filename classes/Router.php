<?php

class Router
{
    private array $routes;

    public function __construct(private string $url)
    {
        $this->routes = require_once ROOT . 'config' . DS . 'routes.php';
    }

    /**
     * Dispatches the request to the appropriate controller and method based on the URL.
     * If no route matches, it shows a 404 error page.
     */
    public function dispatch(): void
    {
        foreach ($this->routes as $path => $values) {
            if (str_contains($this->url, $path)) {
                [$class, $method] = $values['handler'];
                $params = $values['params'] ?? [];
                require_once ROOT . 'controllers' . DS . $class . '.php';
                $controller = new $class();
                call_user_func_array([$controller, $method], $params);
                return;
            }
        }

        // If no route matched, show 404
        http_response_code(404);
        require ROOT . 'views' . DS . '404.php';
    }
}
