<?php

namespace App\Core;

class Controller
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    protected function render(string $viewPathFromViews, array $params = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . ltrim($viewPathFromViews, '/');
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo "Vue introuvable: " . htmlspecialchars($viewPathFromViews);
            exit();
        }

        extract($params, EXTR_SKIP);
        require $viewFile;
    }

    protected function redirect(string $route, array $query = []): void
    {
        $url = $this->basePath . '/index.php';
        $query = array_merge(['route' => $route], $query);
        $url .= '?' . http_build_query($query);
        header("Location: {$url}");
        exit();
    }
}

