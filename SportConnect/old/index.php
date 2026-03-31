<?php
// Compat: certaines anciennes URLs pointent vers /SportConnect/old/index.php
// => on redirige vers le bon endroit selon la session.

require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/session.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'coach') {
        header('Location: ' . BASE_PATH . '/old/espace-coach/dashboard.php');
        exit();
    }

    if (($_SESSION['role'] ?? '') === 'sportif') {
        header('Location: ' . BASE_PATH . '/old/espace-sportif/dashboard.php');
        exit();
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: ' . BASE_PATH . '/old/administration/dashboard.php');
        exit();
    }
}

header('Location: ' . BASE_PATH . '/index.php');
exit();

