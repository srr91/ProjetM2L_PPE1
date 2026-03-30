<?php

require_once __DIR__ . '/../app/bootstrap.php';
\App\Core\Auth::startSession();

$titre = "Conditions d'utilisation - SportConnect";
require_once __DIR__ . '/../app/Views/layout/entete.php';
?>

<div class="container" style="margin-top: 3rem; max-width: 1000px;">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Conditions d'utilisation</h1>
        </div>

        <p style="color: var(--gray); line-height: 1.8;">
            Cette page reprend les conditions d'utilisation de la version précédente.
            Si tu as un contenu spécifique (texte juridique complet), dis-moi et je l'intègre ici.
        </p>

        <p style="color: var(--gray); margin-top: 1.5rem; line-height: 1.8;">
            - Création de compte et utilisation du service<br>
            - Respect et comportement sur la plateforme<br>
            - Données personnelles et confidentialité<br>
            - Responsabilités et limitations
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/layout/pied-de-page.php'; ?>

