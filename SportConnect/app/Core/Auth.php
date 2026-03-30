<?php

namespace App\Core;

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function hasRole(string $role): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    public static function requireLogin(string $loginUrl): void
    {
        if (!self::isLoggedIn()) {
            header("Location: {$loginUrl}");
            exit();
        }
    }

    public static function requireRole(string $role, string $loginUrl, string $fallbackUrl): void
    {
        self::requireLogin($loginUrl);
        if (!self::hasRole($role)) {
            header("Location: {$fallbackUrl}");
            exit();
        }
    }
}

