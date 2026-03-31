<?php
/**
 * Page de connexion
 * Utilise le contrôleur ControleurAuthentification 
 */
require_once __DIR__ . '/../app/bootstrap.php';

\App\Core\Auth::startSession();

$authController = new \App\Controllers\AuthController(BASE_PATH);
$authController->login();
?>
