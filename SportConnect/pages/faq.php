<?php

require_once __DIR__ . '/../app/bootstrap.php';
\App\Core\Auth::startSession();

$titre = 'FAQ - SportConnect';
require_once __DIR__ . '/../app/Views/layout/entete.php';
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="text-align: center; margin-bottom: 3rem;">❓ Foire Aux Questions</h1>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment créer un compte ?</h3>
            <p style="color: var(--gray);">
                Cliquez sur le bouton "Inscription" en haut à droite, choisissez votre profil (Sportif ou Coach),
                remplissez le formulaire et validez.
            </p>
        </div>

        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment trouver un coach ?</h3>
            <p style="color: var(--gray);">
                Allez sur la page "Nos Coachs", utilisez les filtres de recherche (spécialité, tarif, localisation)
                pour trouver le coach qui vous correspond. Consultez leurs profils et réservez une séance.
            </p>
        </div>

        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment réserver une séance ?</h3>
            <p style="color: var(--gray);">
                Sur le profil du coach, cliquez sur "Réserver une séance", choisissez la date et l'heure,
                puis confirmez votre réservation.
            </p>
        </div>

        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Puis-je annuler une séance ?</h3>
            <p style="color: var(--gray);">
                Oui, vous pouvez annuler une séance jusqu'à 24h avant l'heure prévue depuis votre tableau de bord.
                Au-delà, contactez directement le coach.
            </p>
        </div>

        <div style="padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment contacter le support ?</h3>
            <p style="color: var(--gray);">
                Utilisez notre <a href="<?= BASE_PATH ?>/pages/contact.php" style="color: var(--primary);">formulaire de contact</a>
                ou envoyez-nous un email à support@sportconnect.fr.
            </p>
        </div>
    </div>

    <div style="text-align: center; margin: 3rem 0;">
        <p style="color: var(--gray); margin-bottom: 1rem;">Vous ne trouvez pas la réponse à votre question ?</p>
        <a href="<?= BASE_PATH ?>/pages/contact.php" class="btn btn-primary">Contactez-nous</a>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/layout/pied-de-page.php'; ?>

