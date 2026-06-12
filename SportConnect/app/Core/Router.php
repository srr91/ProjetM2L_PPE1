<?php

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function get(string $path, string $controllerClass, string $method): void
    {
        $this->map('GET', $path, $controllerClass, $method);
    }

    public function post(string $path, string $controllerClass, string $method): void
    {
        $this->map('POST', $path, $controllerClass, $method);
    }

    private function map(string $httpMethod, string $path, string $controllerClass, string $method): void
    {
        $path = trim($path, '/');
        $this->routes[strtoupper($httpMethod)][$path] = [$controllerClass, $method];
    }

    public function dispatch(string $httpMethod, string $path, string $basePath): void
    {
        $httpMethod = strtoupper($httpMethod);
        $path = trim($path, '/');

        $handler = $this->routes[$httpMethod][$path] ?? null;

        // Support HEAD requests as GET routes by default.
        if ($handler === null && $httpMethod === 'HEAD') {
            $handler = $this->routes['GET'][$path] ?? null;
        }

        if ($handler === null) {
            http_response_code(404);
            echo "404 - Route introuvable";
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class($basePath);
        $controller->$method();
    }
}

