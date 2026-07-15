<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        // Convert {id} style params into a regex capture group
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', trim($path, '/'));
        $this->routes[] = [
            'method'  => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // remove full match
                [$controllerClass, $action] = $route['handler'];

                $controller = new $controllerClass();
                $request = new Request();

                call_user_func_array([$controller, $action], [$request, ...$matches]);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../../templates/errors/404.php';
    }
}
