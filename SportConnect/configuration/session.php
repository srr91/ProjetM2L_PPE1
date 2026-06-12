<?php
// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Vérifier le rôle de l'utilisateur
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /SportConnect/connexion.php');
        exit();
    }
}

// Rediriger si pas le bon rôle
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /SportConnect/index.php');
        exit();
    }
}

// Déconnexion
function logout() {
    session_destroy();
    header('Location: /SportConnect/index.php');
    exit();
}
?>
