<?php
require_once __DIR__ . '/../app/bootstrap.php';
\App\Core\Auth::startSession();

$authController = new \App\Controllers\AuthController(BASE_PATH);
$authController->logout();
?>
