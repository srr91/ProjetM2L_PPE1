<?php
/**
 * Middleware pour vérifier si le compte utilisateur est toujours actif
 * À inclure sur toutes les pages protégées
 */

if (!function_exists('checkAccountStatus')) {
    function checkAccountStatus() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            return;
        }
        
        require_once __DIR__ . '/database.php';
        
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT actif FROM utilisateurs WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            // Si l'utilisateur n'existe plus ou est désactivé
            if (!$user || $user['actif'] == 0) {
                // Détruire la session
                session_destroy();
                
                // Rediriger vers la page de connexion avec un message
                header('Location: /SportConnect/index.php?route=auth/login&message=compte_desactive');
                exit();
            }
        } catch (Exception $e) {
            // En cas d'erreur, on laisse passer pour ne pas bloquer l'application
            error_log("Erreur vérification compte : " . $e->getMessage());
        }
    }
}

// Appeler automatiquement la vérification
checkAccountStatus();
?>
