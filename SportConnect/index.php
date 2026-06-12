<?php

require_once __DIR__ . '/app/bootstrap.php';

\App\Core\Auth::startSession();

// Conserve ton middleware “compte actif”
require_once __DIR__ . '/configuration/check-account.php';

$router = new \App\Core\Router();

// Routes publiques
$router->get('', \App\Controllers\HomeController::class, 'index');
$router->get('coachs', \App\Controllers\CoachsController::class, 'index');
$router->get('coach/show', \App\Controllers\PublicCoachController::class, 'show');

// Espace coach
$router->get('coach/dashboard', \App\Controllers\CoachAreaController::class, 'dashboard');
$router->post('coach/dashboard', \App\Controllers\CoachAreaController::class, 'dashboard');
$router->get('coach/seances', \App\Controllers\CoachAreaController::class, 'seances');
$router->post('coach/seances', \App\Controllers\CoachAreaController::class, 'seances');

// Espace sportif
$router->get('sportif/reserver', \App\Controllers\SportifAreaController::class, 'reserver');
$router->post('sportif/reserver', \App\Controllers\SportifAreaController::class, 'reserver');

// Authentification
$router->get('auth/login', \App\Controllers\AuthController::class, 'login');
$router->post('auth/login', \App\Controllers\AuthController::class, 'login');
$router->get('auth/forgot', \App\Controllers\AuthController::class, 'forgot');
$router->post('auth/forgot', \App\Controllers\AuthController::class, 'forgot');
$router->get('auth/register', \App\Controllers\AuthController::class, 'register');
$router->post('auth/register', \App\Controllers\AuthController::class, 'register');
$router->get('auth/logout', \App\Controllers\AuthController::class, 'logout');

// Résolution de la route
$route = (string)($_GET['route'] ?? '');
$route = trim($route, '/');

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $route, BASE_PATH);

